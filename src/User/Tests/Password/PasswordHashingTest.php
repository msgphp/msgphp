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

        self::assertNotSame($hashing->hash('password'), $hashing->hash('password'));
        self::assertNotSame($hashing->hash('password'), $hashing->hash('password', $algorithm));
        self::assertNotSame($hashing->hash('password', $algorithm), $hashing->hash('password', $algorithm));
        self::assertNotSame($hashing->hash('password', $algorithm), $hashing->hash('password'));

        if (!$deprecateLegacyApi) {
            self::assertNotSame($hashing->hash('password'), $hashing->hash('password', self::createAlgorithm(true)));
        }

        self::assertNotSame($hashing->hash('password'), $hashing->hash('other-password'));
        self::assertNotSame($hashing->hash('password'), 'password');
    }

    /**
     * @dataProvider provideLegacyApiModes
     */
    public function testIsValid(bool $deprecateLegacyApi): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(), $deprecateLegacyApi);

        self::assertTrue($hashing->isValid($hashing->hash('password'), 'password'));
        self::assertTrue($hashing->isValid($hashing->hash('password'), 'password', $algorithm));
        self::assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password', $algorithm));
        self::assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password'));

        if (!$deprecateLegacyApi) {
            self::assertFalse($hashing->isValid($hashing->hash('password'), 'password', self::createAlgorithm(true)));
        }

        self::assertFalse($hashing->isValid($hashing->hash('password'), $hashing->hash('password')));
        self::assertFalse($hashing->isValid('password', $hashing->hash('password')));
        self::assertFalse($hashing->isValid('password', 'password'));
        self::assertFalse($hashing->isValid($hashing->hash('other-password'), 'password'));
    }

    public function provideLegacyApiModes(): iterable
    {
        yield [true];
        yield [false];
    }

    public function testHashWithLegacyApi(): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(true), false);

        self::assertSame($hashing->hash('password'), $hashing->hash('password'));
        self::assertSame($hashing->hash('password'), $hashing->hash('password', $algorithm));
        self::assertSame($hashing->hash('password', $algorithm), $hashing->hash('password', $algorithm));
        self::assertSame($hashing->hash('password', $algorithm), $hashing->hash('password'));
        self::assertNotSame($hashing->hash('password'), $hashing->hash('password', self::createAlgorithm()));
        self::assertNotSame($hashing->hash('password'), $hashing->hash('other-password'));
        self::assertNotSame($hashing->hash('password'), 'password');
    }

    public function testIsValidWithLegacyApi(): void
    {
        $hashing = new PasswordHashing($algorithm = self::createAlgorithm(true), false);

        self::assertTrue($hashing->isValid($hashing->hash('password'), 'password'));
        self::assertTrue($hashing->isValid($hashing->hash('password'), 'password', $algorithm));
        self::assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password', $algorithm));
        self::assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password'));
        self::assertFalse($hashing->isValid($hashing->hash('password'), 'password', self::createAlgorithm()));
        self::assertFalse($hashing->isValid($hashing->hash('password'), $hashing->hash('password')));
        self::assertFalse($hashing->isValid('password', $hashing->hash('password')));
        self::assertFalse($hashing->isValid('password', 'password'));
        self::assertFalse($hashing->isValid($hashing->hash('other-password'), 'password'));
    }

    public function testBase64EncodedAlgorithm(): void
    {
        $hashing = new PasswordHashing(self::createAlgorithm(true), false);
        $algorithm = PasswordAlgorithm::createLegacyBase64Encoded('md5');
        $algorithm2 = PasswordAlgorithm::createLegacyBase64Encoded('sha1');

        self::assertNotSame($baseHash = $hashing->hash('password'), $hashing->hash('password', $algorithm));
        self::assertSame($hash = base64_encode((string) hex2bin($baseHash)), $hashing->hash('password', $algorithm));
        self::assertNotSame($hash, $hashing->hash('password', $algorithm2));

        self::assertFalse($hashing->isValid($baseHash, 'password', $algorithm));
        self::assertTrue($hashing->isValid($hash, 'password', $algorithm));
        self::assertFalse($hashing->isValid($hash, 'password', $algorithm2));
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

        self::assertNotSame($baseHash = $hashing->hash('password'), $hashing->hash('password', $algorithm));
        self::assertSame($hash = $hashing->hash('password{token}'), $hashing->hash('password', $algorithm));
        self::assertNotSame($hash, $hashing->hash('password', $algorithm2));
        self::assertNotSame($hash, $hashing->hash('password', $algorithmBase64));
        self::assertNotSame($hash, $hashing->hash('password', $algorithmOtherSalt));

        self::assertFalse($hashing->isValid($baseHash, 'password', $algorithm));
        self::assertTrue($hashing->isValid($hash, 'password', $algorithm));
        self::assertFalse($hashing->isValid($hash, 'password', $algorithm2));
        self::assertFalse($hashing->isValid($hash, 'password', $algorithmBase64));
        self::assertFalse($hashing->isValid($hash, 'password', $algorithmOtherSalt));
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

        self::assertNotSame('password', $hashing->hash('password', self::createAlgorithm(true)));
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

        self::assertTrue($hashing->isValid($hashing->hash('password', $algorithm), 'password', $algorithm));
    }

    private static function createAlgorithm(bool $legacy = false): PasswordAlgorithm
    {
        return $legacy ? PasswordAlgorithm::createLegacy('md5') : PasswordAlgorithm::create(\PASSWORD_BCRYPT, ['cost' => 4]);
    }
}
