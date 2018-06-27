<?php

declare(strict_types=1);

namespace MsgPhp\User\Password;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PasswordHashing implements PasswordHashingInterface
{
    private $defaultAlgorithm;
    private $deprecateLegacyApi;

    public function __construct(PasswordAlgorithm $defaultAlgorithm = null, bool $deprecateLegacyApi = true)
    {
        $this->defaultAlgorithm = $defaultAlgorithm ?? PasswordAlgorithm::create();
        $this->deprecateLegacyApi = $deprecateLegacyApi;
    }

    public function hash(string $plainPassword, PasswordAlgorithm $algorithm = null): string
    {
        $algorithm = $algorithm ?? $this->defaultAlgorithm;

        if (!$algorithm->legacy) {
            $hash = password_hash($plainPassword, $algorithm->type, $algorithm->options);

            if (false === $hash) {
                throw new \RuntimeException(sprintf('Unable to hash password with algorithm "%s".', $algorithm->type));
            }

            return $hash;
        }

        if ($this->deprecateLegacyApi) {
            @trigger_error(sprintf('Using PHP\'s legacy password API is deprecated and should be avoided. Create a non-legacy algorithm using "%s::create()" instead.', PasswordAlgorithm::class), \E_USER_DEPRECATED);
        }

        if (null !== $algorithm->salt) {
            if (1 > $algorithm->salt->iterations) {
                throw new \LogicException(sprintf('No. of password salt iterations must be 1 or higher, got %d.', $algorithm->salt->iterations));
            }

            if (2 !== ($c = substr_count($algorithm->salt->format, '%s'))) {
                throw new \LogicException(sprintf('Password salt format should have exactly 2 value placeholders, found %d.', $c));
            }

            $salted = sprintf($algorithm->salt->format, $plainPassword, $algorithm->salt->token);
            $hash = hash($algorithm->type, $salted, true);

            for ($i = 1; $i < $algorithm->salt->iterations; ++$i) {
                $hash = hash($algorithm->type, $hash.$salted, true);
            }
        } else {
            $hash = hash($algorithm->type, $plainPassword, true);
        }

        return $algorithm->encodeBase64 ? base64_encode($hash) : bin2hex($hash);
    }

    public function isValid(string $hashedPassword, string $plainPassword, PasswordAlgorithm $algorithm = null): bool
    {
        $algorithm = $algorithm ?? $this->defaultAlgorithm;

        return $algorithm->legacy
            ? hash_equals($hashedPassword, $this->hash($plainPassword, $algorithm))
            : password_verify($plainPassword, $hashedPassword);
    }
}
