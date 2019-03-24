<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Form\Type;

use MsgPhp\User\Infrastructure\Form\Type\HashedPasswordType;
use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\Password\PasswordHashing;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

final class HashedPasswordTypeTest extends TypeTestCase
{
    public function testDefaultData(): void
    {
        $form = $this->createForm();

        self::assertNull($form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());

        $form = $this->createForm([], 'hash');

        self::assertSame('hash', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testSubmitEmpty($value): void
    {
        $form = $this->createForm();
        $form->submit($value);

        self::assertNull($form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [[]];
        yield [['plain' => null]];
        yield [['plain' => '']];
        yield [['foo' => 'bar']];
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testSubmitInvalid($value): void
    {
        $form = $this->createForm();
        $form->submit($value);

        self::assertNull($form->getData());
        self::assertSame($value, $form->getViewData());
        self::assertFalse($form->isSynchronized());
    }

    public function provideInvalidValues(): iterable
    {
        yield [''];
        yield [['plain' => new \stdClass()]];
    }

    /**
     * @dataProvider provideAlgorithms
     */
    public function testSubmitValid($algorithm, $type): void
    {
        $form = $this->createForm(null === $algorithm ? [] : ['password_algorithm' => $algorithm]);
        $form->submit(['plain' => 'secret']);

        self::assertSame('["secret",'.json_encode($type).']', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
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

    public function testDefaultConfirmation(): void
    {
        $form = $this->createForm(['password_confirm' => true]);
        $form->submit(['confirmation' => null]);

        self::assertNull($form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
    }

    public function testValidConfirmation(): void
    {
        $form = $this->createForm(['password_confirm' => true]);
        $form->submit(['plain' => 'a', 'confirmation' => 'a']);

        self::assertSame('["a",null]', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
    }

    public function testInvalidConfirmation(): void
    {
        $form = $this->createForm(['password_confirm' => true, 'invalid_message' => 'invalid']);
        $form->submit(['plain' => 'a', 'confirmation' => null]);

        self::assertSame('["a",null]', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertSame("ERROR: invalid\n", (string) $form->getErrors(true));
    }

    public function testConfirmationWithoutPassword(): void
    {
        $form = $this->createForm(['password_confirm' => true]);
        $form->submit(['confirmation' => 'a']);

        self::assertNull($form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertSame("ERROR: This value is not valid.\n", (string) $form->getErrors(true));
    }

    public function testCustomHashing(): void
    {
        $hashing = $this->createMock(PasswordHashing::class);
        $hashing->expects(self::once())
            ->method('hash')
            ->with('secret', $algorithm = PasswordAlgorithm::create())
            ->willReturn('custom hash')
        ;

        $form = $this->createForm(['password_hashing' => $hashing, 'password_algorithm' => $algorithm]);
        $form->submit(['plain' => 'secret']);

        self::assertSame('custom hash', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
    }

    public function testCustomOptions(): void
    {
        $form = $this->createForm(['password_options' => ['constraints' => new Callback(function ($value, ExecutionContextInterface $context): void {
            $context->buildViolation('invalid')->addViolation();

            self::assertSame('secret', $value);
        })]]);
        $form->submit(['plain' => 'secret']);

        self::assertSame('["secret",null]', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
        self::assertSame("ERROR: invalid\n", (string) $form->getErrors(true));
    }

    protected function getExtensions(): array
    {
        $hashing = $this->createMock(PasswordHashing::class);
        $hashing->expects(self::any())
            ->method('hash')
            ->willReturnCallback($hasher = function (string $plainPassword, ?PasswordAlgorithm $algorithm): string {
                return (string) json_encode([$plainPassword, null === $algorithm ? null : $algorithm->type]);
            })
        ;
        $hashing->expects(self::any())
            ->method('isValid')
            ->willReturnCallback(function (string $hashedPassword, string $plainPassword, ?PasswordAlgorithm $algorithm) use ($hasher) {
                return $hashedPassword === $hasher($plainPassword, $algorithm);
            })
        ;

        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([new HashedPasswordType($hashing)], []),
        ];
    }

    private function createForm(array $options = [], $data = null): FormInterface
    {
        return $this->factory->create(HashedPasswordType::class, $data, $options);
    }
}
