<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Credential\UsernameCredential;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\UserId;
use Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector as BaseDataCollector;
use Symfony\Bundle\SecurityBundle\Debug\TraceableFirewallListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @todo decorate instead
 *
 * @internal
 */
final class DataCollector extends BaseDataCollector
{
    /**
     * @var UserRepository|null
     */
    private $repository;

    public function __construct(TokenStorageInterface $tokenStorage = null, RoleHierarchyInterface $roleHierarchy = null, LogoutUrlGenerator $logoutUrlGenerator = null, AccessDecisionManagerInterface $accessDecisionManager = null, FirewallMapInterface $firewallMap = null, TraceableFirewallListener $firewall = null, UserRepository $repository = null)
    {
        parent::__construct($tokenStorage, $roleHierarchy, $logoutUrlGenerator, $accessDecisionManager, $firewallMap, $firewall);

        $this->repository = $repository;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        parent::collect($request, $response, $exception);

        if (!isset($this->data['token'])) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $this->data['token'];
        $identity = $token->getUser();

        if ($identity instanceof UserIdentity) {
            $this->data['user'] = $this->getUsername($identity->getUserId());
        }

        if ($token instanceof SwitchUserToken) {
            $impersonatorIdentity = $token->getOriginalToken()->getUser();
        } else {
            // BC Symfony <4.3
            $impersonatorIdentity = null;
            foreach ($token->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatorIdentity = $role->getSource()->getUser();
                }
            }
        }
        if ($impersonatorIdentity instanceof UserIdentity) {
            $this->data['impersonator_user'] = $this->getUsername($impersonatorIdentity->getUserId());
        }
    }

    private function getUsername(UserId $id): string
    {
        if (null === $this->repository) {
            return $id->toString();
        }

        try {
            $credential = $this->repository->find($id)->getCredential();
            if ($credential instanceof UsernameCredential) {
                return $credential->getUsername();
            }
        } catch (EntityNotFoundException $e) {
        }

        return $id->toString();
    }
}
