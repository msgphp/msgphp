<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Security;

use MsgPhp\User\Credential\Credential;
use MsgPhp\User\Credential\PasswordProtectedCredential;
use MsgPhp\User\Infrastructure\Security\UserIdentity;
use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\Password\PasswordSalt;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserIdentityTest extends TestCase
{
    public function testCreate(): void
    {
        $identity = new UserIdentity($entity = $this->createUser(), null, ['ROLE_FOO']);

        self::assertSame($entity->getId(), $identity->getUserId());
        self::assertSame('id', $identity->getUsername());
        self::assertNull($identity->getOriginUsername());
        self::assertSame(['ROLE_FOO'], $identity->getRoles());
        self::assertSame('', $identity->getPassword());
        self::assertNull($identity->getPasswordAlgorithm());
        self::assertNull($identity->getSalt());
    }

    public function testCreateWithOriginUsername(): void
    {
        self::assertSame('origin-username', (new UserIdentity($this->createUser(), 'origin-username'))->getOriginUsername());
    }

    /**
     * @dataProvider providePasswordAlgorithms
     */
    public function testCreateWithPassword(PasswordAlgorithm $algorithm, ?string $salt): void
    {
        $identity = new UserIdentity($this->createUser('password', $algorithm));

        self::assertSame('password', $identity->getPassword());
        self::assertSame($algorithm, $identity->getPasswordAlgorithm());
        self::assertSame($salt, $identity->getSalt());
    }

    /**
     * @dataProvider providePasswordAlgorithms
     */
    public function testEraseCredentials(PasswordAlgorithm $algorithm): void
    {
        $identity = new UserIdentity($this->createUser('password', $algorithm));
        $identity->eraseCredentials();

        self::assertSame('', $identity->getPassword());
        self::assertNull($identity->getPasswordAlgorithm());
        self::assertNull($identity->getSalt());
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

        new UserIdentity($user);
    }

    public function testIsEqualTo(): void
    {
        $identity = new UserIdentity($this->createUser());

        self::assertTrue($identity->isEqualTo($identity));
        self::assertTrue($identity->isEqualTo(new UserIdentity($this->createUser())));
        self::assertTrue($identity->isEqualTo(new UserIdentity($this->createUser('password'))));
        self::assertFalse($identity->isEqualTo(new UserIdentity($this->createUser(null, null, 'other-id'))));
    }

    public function testIsEqualToWithOtherUserType(): void
    {
        $otherIdentity = $this->createMock(UserInterface::class);
        $otherIdentity->expects(self::any())
            ->method('getUsername')
            ->willReturn('id')
        ;

        self::assertFalse((new UserIdentity($this->createUser()))->isEqualTo($otherIdentity));
    }

    public function testIsEqualToWithOtherOriginUsername(): void
    {
        self::assertTrue((new UserIdentity($this->createUser()))->isEqualTo(new UserIdentity($this->createUser(), 'origin-username')));
    }

    public function testSerialize(): void
    {
        $identity = new UserIdentity($this->createUser('password'), 'origin-username', ['ROLE_FOO']);

        self::assertEquals($identity, unserialize(serialize($identity)));
    }

    private function createUser(string $password = null, PasswordAlgorithm $passwordAlgorithm = null, string $id = 'id'): User
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getId')
            ->willReturn(new ScalarUserId($id))
        ;

        if (null === $password) {
            $credential = $this->createMock(Credential::class);
        } else {
            $credential = $this->createMock(PasswordProtectedCredential::class);
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
