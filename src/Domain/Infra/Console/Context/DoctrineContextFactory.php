<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\{DomainCollectionInterface, DomainIdInterface};
use MsgPhp\Domain\Factory\ClassMethodResolver;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineContextFactory implements ContextFactoryInterface
{
    private $contextBuilder;
    private $em;
    private $class;

    public function __construct(ContextFactoryInterface $contextBuilder, EntityManagerInterface $em, string $class = null)
    {
        $this->contextBuilder = $contextBuilder;
        $this->em = $em;
        $this->class;
    }

    public function configure(InputDefinition $definition): void
    {
        // TODO: Implement configure() method.
    }

    public function getContext(InputInterface $input, StyleInterface $io): array
    {
        // TODO: Implement getContext() method.
    }
}
