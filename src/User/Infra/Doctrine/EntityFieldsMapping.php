<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine;

use MsgPhp\Domain\Infra\Doctrine\ObjectFieldMappingProviderInterface;
use MsgPhp\User\Entity\{Credential, Features, Fields, Role, User, UserEmail, UserRole};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class EntityFieldsMapping implements ObjectFieldMappingProviderInterface
{
    private const CREDENTIALS = [
        Features\EmailCredential::class => Credential\Email::class,
        Features\EmailPasswordCredential::class => Credential\EmailPassword::class,
        Features\EmailSaltedPasswordCredential::class => Credential\EmailSaltedPassword::class,
        Features\NicknameCredential::class => Credential\Nickname::class,
        Features\NicknamePasswordCredential::class => Credential\NicknamePassword::class,
        Features\NicknameSaltedPasswordCredential::class => Credential\NicknameSaltedPassword::class,
        Features\TokenCredential::class => Credential\Token::class,
    ];

    public static function getObjectFieldMapping(): array
    {
        $credentials = self::CREDENTIALS;
        array_walk($credentials, function (string &$credential): void {
            $credential = ['credential' => [
                'type' => self::TYPE_EMBEDDED,
                'class' => $credential,
                'columnPrefix' => null,
            ]];
            unset($credential);
        });

        return $credentials + [
            Features\ResettablePassword::class => [
                'passwordResetToken' => [
                    'type' => 'string',
                    'unique' => true,
                    'nullable' => true,
                ],
                'passwordRequestedAt' => [
                    'type' => 'datetime',
                    'nullable' => true,
                ],
            ],
            Fields\EmailsField::class => [
                'emails' => [
                    'type' => self::TYPE_ONE_TO_MANY,
                    'targetEntity' => UserEmail::class,
                    'mappedBy' => 'user',
                    'indexBy' => 'email',
                ],
            ],
            Fields\RoleField::class => [
                'role' => [
                    'type' => self::TYPE_MANY_TO_ONE,
                    'targetEntity' => Role::class,
                    'joinColumns' => [
                        ['referencedColumnName' => 'name', 'nullable' => false],
                    ],
                ],
            ],
            Fields\RolesField::class => [
                'roles' => [
                    'type' => self::TYPE_ONE_TO_MANY,
                    'targetEntity' => UserRole::class,
                    'mappedBy' => 'user',
                    'indexBy' => 'name',
                ],
            ],
            Fields\UserField::class => [
                'user' => [
                    'type' => self::TYPE_MANY_TO_ONE,
                    'targetEntity' => User::class,
                    'joinColumns' => [
                        ['nullable' => false],
                    ],
                ],
            ],
        ];
    }
}
