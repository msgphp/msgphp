<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Password;

use MsgPhp\User\Password\{PasswordAlgorithm, PasswordSalt};
use PHPUnit\Framework\TestCase;

final class PasswordAlgorithmTest extends TestCase
{
    public function testCreate(): void
    {
        $this->assertNonLegacyAlgorithm(\PASSWORD_DEFAULT, [], PasswordAlgorithm::create());
        $this->assertNonLegacyAlgorithm(-1, ['foo' => 'bar'], PasswordAlgorithm::create(-1, ['foo' => 'bar']));
    }

    public function testCreateLegacy(): void
    {
        $this->assertLegacyAlgorithm(PasswordAlgorithm::DEFAULT_LEGACY, null, false, PasswordAlgorithm::createLegacy());
        $this->assertLegacyAlgorithm('foo', null, false, PasswordAlgorithm::createLegacy('foo'));
    }

    public function testCreateLegacyBase64Encoded(): void
    {
        $this->assertLegacyAlgorithm(PasswordAlgorithm::DEFAULT_LEGACY, null, true, PasswordAlgorithm::createLegacyBase64Encoded());
        $this->assertLegacyAlgorithm('foo', null, true, PasswordAlgorithm::createLegacyBase64Encoded('foo'));
    }

    public function testCreateLegacyWithSalt(): void
    {
        $salt = new PasswordSalt('token');

        $this->assertLegacyAlgorithm(PasswordAlgorithm::DEFAULT_LEGACY, $salt, true, PasswordAlgorithm::createLegacySalted($salt));
        $this->assertLegacyAlgorithm(PasswordAlgorithm::DEFAULT_LEGACY, $salt, true, PasswordAlgorithm::createLegacySalted($salt, true));
        $this->assertLegacyAlgorithm('foo', $salt, true, PasswordAlgorithm::createLegacySalted($salt, true, 'foo'));
        $this->assertLegacyAlgorithm(PasswordAlgorithm::DEFAULT_LEGACY, $salt, false, PasswordAlgorithm::createLegacySalted($salt, false));
        $this->assertLegacyAlgorithm('foo', $salt, false, PasswordAlgorithm::createLegacySalted($salt, false, 'foo'));
    }

    private function assertNonLegacyAlgorithm(int $type, array $options, PasswordAlgorithm $algorithm): void
    {
        $this->assertSame($type, $algorithm->type);
        $this->assertSame($options, $algorithm->options);
        $this->assertFalse($algorithm->legacy);
        $this->assertNull($algorithm->salt);
        $this->assertNull($algorithm->encodeBase64);
    }

    private function assertLegacyAlgorithm(string $type, ?PasswordSalt $salt, bool $encodeBase64, PasswordAlgorithm $algorithm): void
    {
        $this->assertSame($type, $algorithm->type);
        $this->assertNull($algorithm->options);
        $this->assertTrue($algorithm->legacy);
        $this->assertSame($salt, $algorithm->salt);
        $this->assertSame($encodeBase64, $algorithm->encodeBase64);
    }
}
