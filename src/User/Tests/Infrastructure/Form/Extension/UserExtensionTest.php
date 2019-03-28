<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Form\Extension;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Infrastructure\Form\Extension\UserExtension;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

final class UserExtensionTest extends FormIntegrationTestCase
{
    public function testSubmit(): void
    {
        $form = $this->factory
            ->createBuilder(FormType::class, [], ['user_mapping' => ['username' => 'user']])
            ->add('username', TextType::class)
            ->getForm()
        ;

        $form->submit(['username' => 'some']);
        $data = $form->getData();

        self::assertArrayHasKey('username', $data);
        self::assertSame('some', $data['username']);
        self::assertArrayHasKey('user', $data);
        self::assertInstanceOf(User::class, $data['user']);
    }

    public function testSubmitUnknown(): void
    {
        $form = $this->factory
            ->createBuilder(FormType::class, [], ['user_mapping' => ['username' => 'user']])
            ->add('username', TextType::class)
            ->getForm()
        ;

        $form->submit(['username' => 'unknown']);
        $data = $form->getData();

        self::assertArrayHasKey('username', $data);
        self::assertSame('unknown', $data['username']);
        self::assertArrayHasKey('user', $data);
        self::assertNull($data['user']);
    }

    protected function getTypeExtensions(): array
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::any())
            ->method('findByUsername')
            ->willReturnCallback(function (string $username): User {
                if ('unknown' === $username) {
                    throw EntityNotFoundException::createForFields(User::class, ['username' => $username]);
                }

                return $this->getMockForAbstractClass(User::class);
            })
        ;

        return [new UserExtension($repository)];
    }
}
