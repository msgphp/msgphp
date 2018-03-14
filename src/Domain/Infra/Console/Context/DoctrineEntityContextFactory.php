<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use MsgPhp\Domain\Exception\InvalidClassException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineEntityContextFactory implements ContextFactoryInterface
{
    private $factory;
    private $em;
    private $class;
    private $discriminatorField;

    public function __construct(ContextFactoryInterface $factory, EntityManagerInterface $em, string $class)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->class = $class;
    }

    public function configure(InputDefinition $definition): void
    {
        $this->discriminatorField = null;
        $metadata = $this->getMetadata();

        if (isset($metadata->discriminatorColumn['fieldName'])) {
            $definition->addOption(new InputOption(
                $this->discriminatorField = ClassContextFactory::getUniqueFieldName($definition, $metadata->discriminatorColumn['fieldName']),
                null,
                InputOption::VALUE_OPTIONAL,
                'The entity discriminator value'
            ));
        }

        $this->factory->configure($definition);
    }

    public function getContext(InputInterface $input, StyleInterface $io, array $values = []): array
    {
        $context = [];

        if (null !== $this->discriminatorField) {
            $metadata = $this->getMetadata();
            $key = $metadata->discriminatorColumn['fieldName'];

            if (isset($values[$key])) {
                $context[$key] = $values[$key];
                unset($values[$key]);
            } elseif (null === $value = $input->getOption($this->discriminatorField)) {
                $context[$key] = $io->choice('Select entity discriminator', array_keys($metadata->discriminatorMap), $metadata->discriminatorValue);
            } elseif (isset($metadata->discriminatorMap[$value])) {
                $context[$key] = $value;
            } elseif (false !== $found = array_search($value, $metadata->discriminatorMap, true)) {
                $context[$key] = $found;
            } else {
                throw new \LogicException(sprintf('Invalid entity discriminator "%s" provided.', $value));
            }

            // @todo add feature to ask additional context values, required by the provided discriminator class
        }

        return $context + $this->factory->getContext($input, $io, $values);
    }

    private function getMetadata(): ClassMetadata
    {
        if (!class_exists($this->class) || $this->em->getMetadataFactory()->isTransient($this->class)) {
            throw InvalidClassException::create($this->class);
        }

        return $this->em->getClassMetadata($this->class);
    }
}
