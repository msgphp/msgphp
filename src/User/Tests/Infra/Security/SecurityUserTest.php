<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\User\Credential\CredentialInterface;
use MsgPhp\User\Credential\PasswordProtectedCredentialInterface;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\Password\PasswordSalt;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new SecurityUser($entity = $this->createUser(), null, ['ROLE_FOO']);

        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('id', $user->getUsername());
        self::assertNull($user->getOriginUsername());
        self::assertSame(['ROLE_FOO'], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getPasswordAlgorithm());
        self::assertNull($user->getSalt());
    }

    public function testCreateWithOriginUsername(): void
    {
        self::assertSame('origin-username', (new SecurityUser($this->createUser(), 'origin-username'))->getOriginUsername());
    }

    /**
     * @dataProvider providePasswordAlgorithms
     */
    public function testCreateWithPassword(PasswordAlgorithm $algorithm, ?string $salt): void
    {
        $user = new SecurityUser($this->createUser('password', $algorithm));

        self::assertSame('password', $user->getPassword());
        self::assertSame($algorithm, $user->getPasswordAlgorithm());
        self::assertSame($salt, $user->getSalt());
    }

    /**
     * @dataProvider providePasswordAlgorithms
     */
    public function testEraseCredentials(PasswordAlgorithm $algorithm): void
    {
        $user = new SecurityUser($this->createUser('password', $algorithm));
        $user->eraseCredentials();

        self::assertSame('', $user->getPassword());
        self::assertNull($user->getPasswordAlgorithm());
        self::assertNull($user->getSalt());
    }

    public function providePasswordAlgorithms(): iterable
    {
        yield [PasswordAlgorithm::create(), null];
        yield [PasswordAlgorithm::createLegacySalted(new PasswordSalt('salt')), 'salt'];
    }

    public function testCreateWithEmptyId(): void
    {
        $user = $this->createMock(User::class);
        $user->expects(self::once())
            ->method('getId')
            ->willReturn(new ScalarUserId())
        ;

        $this->expectException(\LogicException::class);

        new SecurityUser($user);
    }

    public function testIsEqualTo(): void
    {
        $user = new SecurityUser($this->createUser());

        self::assertTrue($user->isEqualTo($user));
        self::assertTrue($user->isEqualTo(new SecurityUser($this->createUser())));
        self::assertTrue($user->isEqualTo(new SecurityUser($this->createUser('password'))));
        self::assertFalse($user->isEqualTo(new SecurityUser($this->createUser(null, null, 'other-id'))));
    }

    public function testIsEqualToWithOtherUserType(): void
    {
        $other = $this->createMock(UserInterface::class);
        $other->expects(self::any())
            ->method('getUsername')
            ->willReturn('id')
        ;

        self::assertFalse((new SecurityUser($this->createUser()))->isEqualTo($other));
    }

    public function testIsEqualToWithOtherOriginUsername(): void
    {
        self::assertTrue((new SecurityUser($this->createUser()))->isEqualTo(new SecurityUser($this->createUser(), 'origin-username')));
    }

    public function testSerialize(): void
    {
        $user = new SecurityUser($this->createUser('password'), 'origin-username', ['ROLE_FOO']);

        self::assertEquals($user, unserialize(serialize($user)));
    }

    private function createUser(string $password = null, PasswordAlgorithm $passwordAlgorithm = null, string $id = 'id'): User
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getId')
            ->willReturn(new ScalarUserId($id))
        ;

        if (null === $password) {
            $credential = $this->createMock(CredentialInterface::class);
        } else {
            $credential = $this->createMock(PasswordProtectedCredentialInterface::class);
            $credential->expects(self::any())
                ->method('getPassword')
                ->willReturn($password)
            ;
            $credential->expects(self::any())
                ->method('getPasswordAlgorithm')
                ->willReturn($passwordAlgorithm ?? PasswordAlgorithm::create())
            ;
        }

        $user->expects(self::any())
            ->method('getCredential')
            ->willReturn($credential)
        ;

        return $user;
    }
}
