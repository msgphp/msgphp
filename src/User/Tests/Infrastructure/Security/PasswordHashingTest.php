<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Security;

use MsgPhp\User\Infrastructure\Security\PasswordHashing;
use MsgPhp\User\Password\PasswordAlgorithm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

final class PasswordHashingTest extends TestCase
{
    public function testSelfSaltingEncoderIsRequired(): void
    {
        $this->expectException(\LogicException::class);

        new PasswordHashing($this->createMock(PasswordEncoderInterface::class));
    }

    public function testHash(): void
    {
        $encoder = $this->createMock([PasswordEncoderInterface::class, SelfSaltingEncoderInterface::class]);
        $encoder->expects(self::once())
            ->method('encodePassword')
            ->with('password', '')
            ->willReturn('hash')
        ;

        self::assertSame('hash', (new PasswordHashing($encoder))->hash('password'));
    }

    public function testHashWithCustomAlgorithm(): void
    {
        $encoder = $this->createMock([PasswordEncoderInterface::class, SelfSaltingEncoderInterface::class]);
        $hashing = new PasswordHashing($encoder);

        $this->expectException(\LogicException::class);

        $hashing->hash('password', PasswordAlgorithm::create());
    }

    public function testIsValid(): void
    {
        $encoder = $this->createMock([PasswordEncoderInterface::class, SelfSaltingEncoderInterface::class]);
        $encoder->expects(self::once())
            ->method('isPasswordValid')
            ->with('hash', 'password', '')
            ->willReturn(true)
        ;

        self::assertTrue((new PasswordHashing($encoder))->isValid('hash', 'password'));
    }

    public function testIsValidWithCustomAlgorithm(): void
    {
        $encoder = $this->createMock([PasswordEncoderInterface::class, SelfSaltingEncoderInterface::class]);
        $hashing = new PasswordHashing($encoder);

        $this->expectException(\LogicException::class);

        $hashing->isValid('hash', 'password', PasswordAlgorithm::create());
    }
}
