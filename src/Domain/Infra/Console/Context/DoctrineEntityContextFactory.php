<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineEntityContextFactory implements ContextFactoryInterface
{
    private $factory;
    private $em;
    private $class;

    public function __construct(ContextFactoryInterface $factory, EntityManagerInterface $em, string $class)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->class = $class;
    }

    public function configure(InputDefinition $definition): void
    {
    }

    public function getContext(InputInterface $input, StyleInterface $io, array $values = []): array
    {
    }
}
