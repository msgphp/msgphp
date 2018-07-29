<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Console\Command;

use MsgPhp\User\Entity\Role;
use MsgPhp\User\Repository\RoleRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait RoleAwareTrait
{
    /** @var RoleRepositoryInterface */
    private $repository;

    protected function getRole(InputInterface $input, StyleInterface $io): Role
    {
        if (null === $value = $input->getArgument('role')) {
            if (!$input->isInteractive()) {
                throw new \LogicException('No value provided for "role".');
            }

            do {
                $value = $io->ask('Role name');
            } while (null === $value);
        }

        return $this->repository->find($value);
    }
}
