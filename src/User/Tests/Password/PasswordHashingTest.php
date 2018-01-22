<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Password;

use MsgPhp\User\Password\{PasswordAlgorithm, PasswordHashing, PasswordSalt};
use PHPUnit\Framework\TestCase;

final class PasswordHashingTest extends TestCase
{
    /**
     * @dataProvider provideLegacyApiModes
     */
    public function testHash(bool $deprecateLegacyApi): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(), $deprecateLegacyApi);

        $this->assertNotSame($hashing->hash('password'), $hashing->hash('password'));
        $this->assertNotSame($hashing->hash('password'), $hashing->hash('password', $algorithm));
        $this->assertNotSame($hashing->hash('password', $algorithm), $hashing->hash('password', $algorithm));
        $this->assertNotSame($hashing->hash('password', $algorithm), $hashing->hash('password'));

        if (!$deprecateLegacyApi) {
            $this->assertNotSame($hashing->hash('password'), $hashing->hash('password', self::createAlgorithm(true)));
        }

        $this->assertNotSame($hashing->hash('password'), $hashing->hash('other-password'));
        $this->assertNotSame($hashing->hash('password'), 'password');
    }

    /**
     * @dataProvider provideLegacyApiModes
     */
    public function testIsValid(bool $deprecateLegacyApi): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(), $deprecateLegacyApi);

        $this->assertTrue($hashing->isValid($hashing->hash('password'), 'password'));
        $this->assertTrue($hashing->isValid($hashing->hash('password'), 'password', $algorithm));
        $this->assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password', $algorithm));
        $this->assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password'));

        if (!$deprecateLegacyApi) {
            $this->assertFalse($hashing->isValid($hashing->hash('password'), 'password', self::createAlgorithm(true)));
        }

        $this->assertFalse($hashing->isValid($hashing->hash('password'), $hashing->hash('password')));
        $this->assertFalse($hashing->isValid('password', $hashing->hash('password')));
        $this->assertFalse($hashing->isValid('password', 'password'));
        $this->assertFalse($hashing->isValid($hashing->hash('other-password'), 'password'));
    }

    public function provideLegacyApiModes(): iterable
    {
        yield [true];
        yield [false];
    }

    public function testHashWithLegacyApi(): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(true), false);

        $this->assertSame($hashing->hash('password'), $hashing->hash('password'));
        $this->assertSame($hashing->hash('password'), $hashing->hash('password', $algorithm));
        $this->assertSame($hashing->hash('password', $algorithm), $hashing->hash('password', $algorithm));
        $this->assertSame($hashing->hash('password', $algorithm), $hashing->hash('password'));
        $this->assertNotSame($hashing->hash('password'), $hashing->hash('password', self::createAlgorithm()));
        $this->assertNotSame($hashing->hash('password'), $hashing->hash('other-password'));
        $this->assertNotSame($hashing->hash('password'), 'password');
    }

    public function testIsValidWithLegacyApi(): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(true), false);

        $this->assertTrue($hashing->isValid($hashing->hash('password'), 'password'));
        $this->assertTrue($hashing->isValid($hashing->hash('password'), 'password', $algorithm));
        $this->assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password', $algorithm));
        $this->assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password'));
        $this->assertFalse($hashing->isValid($hashing->hash('password'), 'password', self::createAlgorithm()));
        $this->assertFalse($hashing->isValid($hashing->hash('password'), $hashing->hash('password')));
        $this->assertFalse($hashing->isValid('password', $hashing->hash('password')));
        $this->assertFalse($hashing->isValid('password', 'password'));
        $this->assertFalse($hashing->isValid($hashing->hash('other-password'), 'password'));
    }

    public function testBase64EncodedAlgorithm(): void
    {
        $hashing = new PasswordHashing(self::createAlgorithm(true), false);
        $algorithm = PasswordAlgorithm::createLegacyBase64Encoded('md5');
        $algorithm2 = PasswordAlgorithm::createLegacyBase64Encoded('sha1');

        $this->assertNotSame($baseHash = $hashing->hash('password'), $hashing->hash('password', $algorithm));
        $this->assertSame($hash = base64_encode(hex2bin($baseHash)), $hashing->hash('password', $algorithm));
        $this->assertNotSame($hash, $hashing->hash('password', $algorithm2));

        $this->assertFalse($hashing->isValid($baseHash, 'password', $algorithm));
        $this->assertTrue($hashing->isValid($hash, 'password', $algorithm));
        $this->assertFalse($hashing->isValid($hash, 'password', $algorithm2));
    }

    /**
     * @dataProvider provideOtherPasswordSalts
     */
    public function testSaltedAlgorithm(PasswordSalt $otherSalt): void
    {
        $hashing = new PasswordHashing(self::createAlgorithm(true), false);
        $algorithm = PasswordAlgorithm::createLegacySalted($salt = new PasswordSalt('token', 1), false, 'md5');
        $algorithm2 = PasswordAlgorithm::createLegacySalted($salt, false, 'sha1');
        $algorithmBase64 = PasswordAlgorithm::createLegacySalted($salt, true, 'md5');
        $algorithmOtherSalt = PasswordAlgorithm::createLegacySalted($otherSalt, false, 'md5');

        $this->assertNotSame($baseHash = $hashing->hash('password'), $hashing->hash('password', $algorithm));
        $this->assertSame($hash = $hashing->hash('password{token}'), $hashing->hash('password', $algorithm));
        $this->assertNotSame($hash, $hashing->hash('password', $algorithm2));
        $this->assertNotSame($hash, $hashing->hash('password', $algorithmBase64));
        $this->assertNotSame($hash, $hashing->hash('password', $algorithmOtherSalt));

        $this->assertFalse($hashing->isValid($baseHash, 'password', $algorithm));
        $this->assertTrue($hashing->isValid($hash, 'password', $algorithm));
        $this->assertFalse($hashing->isValid($hash, 'password', $algorithm2));
        $this->assertFalse($hashing->isValid($hash, 'password', $algorithmBase64));
        $this->assertFalse($hashing->isValid($hash, 'password', $algorithmOtherSalt));
    }

    public function provideOtherPasswordSalts(): iterable
    {
        yield [new PasswordSalt('other', 1)];
        yield [new PasswordSalt('token', 2)];
        yield [new PasswordSalt('token', 1, '%s %s')];
    }

    /**
     * @dataProvider provideInvalidSaltIterations
     */
    public function testSaltedAlgorithmWithInvalidIteration(int $iteration): void
    {
        $hashing = new PasswordHashing(PasswordAlgorithm::createLegacySalted(new PasswordSalt('token', $iteration), false, 'md5'), false);

        $this->expectException(\LogicException::class);

        $hashing->hash('password');
    }

    public function provideInvalidSaltIterations(): iterable
    {
        yield [0];
        yield [-1];
    }

    /**
     * @dataProvider provideInvalidSaltFormats
     */
    public function testSaltedAlgorithmWithInvalidFormat(string $format): void
    {
        $hashing = new PasswordHashing(PasswordAlgorithm::createLegacySalted(new PasswordSalt('token', 1, $format), false, 'md5'), false);

        $this->expectException(\LogicException::class);

        $hashing->hash('password');
    }

    public function provideInvalidSaltFormats(): iterable
    {
        yield ['foo'];
        yield ['foo %s'];
        yield ['foo %s %s %s'];
    }

    /**
     * NOT A LEGACY TEST.
     *
     * @group legacy
     * @expectedDeprecation Using PHP's legacy password API is deprecated and should be avoided. Create a non-legacy algorithm using "MsgPhp\User\Password\PasswordAlgorithm::create()" instead.
     */
    public function testHashWithLegacyAlgorithm(): void
    {
        $hashing = new PasswordHashing();

        $this->assertNotSame('password', $hashing->hash('password', self::createAlgorithm(true)));
    }

    /**
     * NOT A LEGACY TEST.
     *
     * @group legacy
     * @expectedDeprecation Using PHP's legacy password API is deprecated and should be avoided. Create a non-legacy algorithm using "MsgPhp\User\Password\PasswordAlgorithm::create()" instead.
     */
    public function testIsValidhWithLegacyAlgorithm(): void
    {
        $hashing = new PasswordHashing();
        $algorithm = self::createAlgorithm(true);

        $this->assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password', $algorithm));
    }

    private static function createAlgorithm(bool $legacy = false): PasswordAlgorithm
    {
        return $legacy ? PasswordAlgorithm::createLegacy('md5') : PasswordAlgorithm::create(\PASSWORD_BCRYPT, ['cost' => 4]);
    }
}
