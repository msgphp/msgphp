<?php

declare(strict_types=1);

namespace MsgPhp\UserBundle\Maker;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity;
use MsgPhp\User\UserIdInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserMaker implements MakerInterface
{
    private $classMapping;
    private $writes = [];

    public function __construct(array $classMapping)
    {
        $this->classMapping = $classMapping;
    }

    public static function getCommandName(): string
    {
        return 'make:user';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        if (!isset($this->classMapping[Entity\User::class])) {
            throw new \LogicException('User class not configured. Did you install the bundle using Symfony Recipes?');
        }

        if (isset($this->classMapping[CredentialInterface::class]) && $io->confirm('Generate a built-in user credential?')) {
            $this->generateCredential(new \ReflectionClass($this->classMapping[Entity\User::class]), $io);
        }

        while ($write = array_pop($this->writes)) {
            [$fileName, $contents] = $write;

            if ($io->confirm(sprintf('Write changes to "%s"?', $fileName))) {
                dump($fileName, $contents);
            }
        }
    }

    private function generateCredential(\ReflectionClass $targetClass, ConsoleStyle $io): void
    {
        $credentials = [];
        foreach (glob(dirname((new \ReflectionClass(UserIdInterface::class))->getFileName()).'/Entity/Credential/*.php') as $file) {
            if ('Anonymous' === $credential = basename($file, '.php')) {
                continue;
            }
            $credentials[] = $credential;
        }
        $credential = $io->choice('Select credential type', $credentials, 'EmailPassword');
        $trait = 'MsgPhp\\User\\Entity\\Features\\'.$credential.'Credential';
        $class = new \ReflectionClass('MsgPhp\\User\\Entity\\Credential\\'.$credential);

        dump($trait, $class);

        $lines = file($fileName = $targetClass->getFileName());
        $write = false;
        $inClass = $inClassBody = $hasImplements = false;
        $useLine = $traitUseLine = $implementsLine = 0;
        $nl = null;

        foreach ($tokens = token_get_all(implode('', $lines)) as $i => $token) {
            if (!is_array($token)) {
                if ('{' === $token && $inClass && !$inClassBody) {
                    $inClassBody = true;
                }
                continue;
            }
            if (in_array($token[0], [\T_COMMENT, \T_DOC_COMMENT, \T_WHITESPACE], true)) {
                if (!$nl && \T_WHITESPACE === $token[0]) {
                    $nl = in_array($nl = trim($token[1], ' '), ["\n", "\r", "\r\n"], true) ? $nl : null;
                }
                continue;
            }
            if (\T_NAMESPACE === $token[0] && !$useLine) {
                $useLine = $token[2];
            } elseif (\T_CLASS === $token[0] && !$inClass) {
                $inClass = true;
            } elseif (\T_USE === $token[0]) {
                if (!$inClass) {
                    $useLine = $token[2];
                } else {
                    $traitUseLine = $token[2];
                }
            } elseif (\T_EXTENDS === $token[0] && $inClass) {
                $implementsLine = $tokens[2];
                $j = $i + 1;
                while (isset($tokens[$j])) {
                    if (isset($tokens[$j][0]) && \T_STRING === $tokens[$j][0]) {
                        $implementsLine = $tokens[$j][2];
                    } elseif ('{' === $tokens[$j] || (isset($tokens[$j][0]) && \T_IMPLEMENTS === $tokens[$j][0])) {
                        break;
                    }
                    ++$j;
                }
            } elseif (\T_IMPLEMENTS === $token[0] && $inClass) {
                $hasImplements = true;
                $implementsLine = $token[2];
                $j = $i + 1;
                while (isset($tokens[$j])) {
                    if (is_array($tokens[$j]) && \T_STRING === $tokens[$j][0]) {
                        $implementsLine = $tokens[$j][2];
                    } elseif ('{' === $tokens[$j]) {
                        break;
                    }
                    ++$j;
                }
            } elseif ($inClassBody && !$traitUseLine) {
                $traitUseLine = $token[2];
            }
        }
        $nl = $nl ?? \PHP_EOL;

        dump($nl, $useLine, $traitUseLine, $hasImplements, $implementsLine);

        if (null !== $constructor = $targetClass->getConstructor()) {
            $offset = $constructor->getStartLine() - 1;
            $length = $constructor->getEndLine() - $offset;
            $contents = preg_replace_callback_array([
                '~^[^_]*+__construct\([^\)]*+\)~i' => function (array $match) use ($class): string {
                    $signature = substr($match[0], 0, -1);
                    if ('' !== $origin = self::getCredentialConstructorArgs($class)) {
                        $signature .= ('(' !== substr(rtrim($signature), -1) ? ', ' : '').$origin;
                    }

                    return $signature.')';
                },
                '~\s*+}\s*+$~s' => function ($match) use ($nl, $credential): string {
                    $indent = ltrim(substr($match[0], 0, strpos($match[0], '}')), "\r\n").'    ';

                    return $nl.$indent.'$this->credential = new '.$credential.'($email);'.$match[0];
                }
            ], implode('', array_slice($lines, $offset, $length)));

            array_splice($lines, $offset, $length, $contents);
            $write = true;
        }

        if ($write) {
            $this->writes[] = [$fileName, implode('', $lines)];
        }
    }

    private static function getCredentialConstructorArgs(\ReflectionClass $class): string
    {
        if (null === $constructor = $class->getConstructor()) {
            return '';
        }

        $lines = file($class->getFileName());
        $offset = $constructor->getStartLine() - 1;
        $body = implode('', array_slice($lines, $offset, $constructor->getEndLine() - $offset));

        if (preg_match('~^[^_]*+__construct\(([^\)]++)\)~i', $body, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
