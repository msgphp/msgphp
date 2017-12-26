<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SecurityUserChecker implements UserCheckerInterface
{
    private $repository;
    private $logger;

    public function __construct(UserRepositoryInterface $repository, LoggerInterface $logger = null)
    {
        $this->repository = $repository;
        $this->logger = $logger ?? new NullLogger();
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof SecurityUser) {
            return;
        }

        $user = $this->repository->find($user->getId());

        if (!$user->isEnabled()) {
            $this->logger->info('Disabled user login attempt.', ['id' => $user->getId()->toString(), 'email' => $user->getEmail()]);

            throw new DisabledException(sprintf('User "%s" is disabled.', $user->getEmail()));
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
