<?php

declare(strict_types=1);

namespace MsgPhp\UserBundle\Maker;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity;
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

        if (isset($this->classMapping[CredentialInterface::class])) {
            $reflection = new \ReflectionClass($this->classMapping[Entity\User::class]);
            $lines = file($fileName = $reflection->getFileName());

            if (null !== $constructor = $reflection->getConstructor()) {
                $start = $constructor->getStartLine() - 1;
                $end = $constructor->getEndLine();
                $contents = preg_replace_callback_array([
                    '~^[^_]*+__construct\([^\)]*+\)~i' => function (array $match): string {
                        $signature = substr($match[0], 0, -1);
                        if ('(' !== substr(rtrim($signature), -1)) {
                            $signature .= ', ';
                        }
                        $signature .= 'string $email';

                        return $signature.')';
                    },
                    '~\s*+}\s*+$~s' => function ($match): string {
                        $indent = ltrim(substr($match[0], 0, strpos($match[0], '}')), "\r\n").'    ';

                        return \PHP_EOL.$indent.'$this->credential = new Email($email);'.$match[0];
                    }
                ], implode('', array_slice($lines, $start, $end - $start)));
                array_splice($lines, $start, $end - $start, $contents);

                if ($io->confirm(sprintf('Write changes to %s?', $fileName))) {
                    file_put_contents($fileName, implode('', $lines));
                }
            }
        }
    }
}
