<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console\Definition;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use MsgPhp\Domain\Exception\InvalidClass;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineContextDefinition implements DomainContextDefinition
{
    private $definition;
    private $em;
    private $class;
    /** @var string|null */
    private $discriminatorField;

    /**
     * @param class-string $class
     */
    public function __construct(DomainContextDefinition $definition, EntityManagerInterface $em, string $class)
    {
        $this->definition = $definition;
        $this->em = $em;
        $this->class = $class;
    }

    public function configure(InputDefinition $definition): void
    {
        $this->discriminatorField = null;
        $metadata = $this->getMetadata();

        if (isset($metadata->discriminatorColumn['fieldName'])) {
            $definition->addOption(new InputOption(
                $this->discriminatorField = ClassContextDefinition::getUniqueFieldName($definition, $metadata->discriminatorColumn['fieldName']),
                null,
                InputOption::VALUE_OPTIONAL,
                'The entity discriminator value'
            ));
        }

        $this->definition->configure($definition);
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
                $context[$key] = $io->choice('Select discriminator', array_keys($metadata->discriminatorMap), $metadata->discriminatorValue);
            } elseif (isset($metadata->discriminatorMap[$value])) {
                $context[$key] = $value;
            } elseif (false !== $found = array_search($value, $metadata->discriminatorMap, true)) {
                $context[$key] = $found;
            } else {
                throw new \LogicException('Invalid entity discriminator '.json_encode($value).' provided.');
            }

            // @todo ask additional context values, required by the provided discriminator class
        }

        return $context + $this->definition->getContext($input, $io, $values);
    }

    private function getMetadata(): ClassMetadata
    {
        if (!class_exists($this->class) || $this->em->getMetadataFactory()->isTransient($this->class)) {
            throw InvalidClass::create($this->class);
        }

        return $this->em->getClassMetadata($this->class);
    }
}
