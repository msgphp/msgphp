<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Form\Type;

use MsgPhp\User\Infra\Form\Type\HashedPasswordType;
use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\Password\PasswordHashingInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class HashedPasswordTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function testDefaultData(): void
    {
        $form = $this->createForm();

        self::assertNull($form->getData());

        $form = $this->createForm([], $data = ['password' => 'data']);

        self::assertSame($data, $form->getData());
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testSubmitEmpty($value): void
    {
        $form = $this->createForm();
        $form->submit(['password' => $value]);

        self::assertSame(['password' => null], $form->getData());
        self::assertTrue($form->isValid());
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    public function testSubmitInvalid(): void
    {
        $form = $this->createForm();
        $form->submit(['password' => new \stdClass()]);

        self::assertSame([], $form->getData());
    }

    /**
     * @dataProvider provideAlgorithms
     */
    public function testSubmitValid($algorithm, $type): void
    {
        $form = $this->createForm(null === $algorithm ? [] : ['password_algorithm' => $algorithm]);
        $form->submit(['password' => 'secret']);

        self::assertSame(['password' => '["secret",'.json_encode($type).']'], $form->getData());
    }

    public function provideAlgorithms(): iterable
    {
        yield [null, null];
        yield [\PASSWORD_BCRYPT, 1];
        yield ['md5', 'md5'];
        yield [PasswordAlgorithm::createLegacy('md2'), 'md2'];
        yield [function (): PasswordAlgorithm {
            return PasswordAlgorithm::create(2);
        }, 2];
    }

    protected function getExtensions(): array
    {
        $passwordHashing = $this->createMock(PasswordHashingInterface::class);
        $passwordHashing->expects(self::any())
            ->method('hash')
            ->willReturnCallback($hasher = function (string $plainPassword, ?PasswordAlgorithm $algorithm): string {
                return (string) json_encode([$plainPassword, null === $algorithm ? null : $algorithm->type]);
            });
        $passwordHashing->expects(self::any())
            ->method('isValid')
            ->willReturnCallback(function (string $hashedPassword, string $plainPassword, ?PasswordAlgorithm $algorithm) use ($hasher) {
                return $hashedPassword === $hasher($plainPassword, $algorithm);
            });
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        return [
            new PreloadedExtension([new HashedPasswordType($passwordHashing, $tokenStorage)], []),
            $this->getValidatorExtension(),
        ];
    }

    private function createForm(array $options = [], $data = null): FormInterface
    {
        return $this->factory->create(HashedPasswordType::class, $data, $options + ['inherit_data' => false]);
    }
}
