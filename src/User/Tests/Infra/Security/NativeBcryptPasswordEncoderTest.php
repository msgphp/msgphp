<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\User\Infra\Security\NativeBcryptPasswordEncoder;
use PHPUnit\Framework\TestCase;

final class NativeBcryptPasswordEncoderTest extends TestCase
{
    public function testEncode(): void
    {
        $encoder = new NativeBcryptPasswordEncoder();

        $this->assertNotSame('password', $encoder->encode('password'));
        $this->assertNotSame($encoder->encode('other-password'), $encoder->encode('password'));
    }

    public function testIsValid(): void
    {
        $encoder = new NativeBcryptPasswordEncoder();

        $this->assertTrue($encoder->isValid($encoder->encode('password'), 'password'));
        $this->assertFalse($encoder->isValid($encoder->encode('other-password'), 'password'));
    }
}
