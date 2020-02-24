<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Form\Type;

use MsgPhp\User\Infrastructure\Form\Type\HashedPasswordType;
use MsgPhp\User\Infrastructure\Security\UserIdentity;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @internal
 */
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
     *
     * @param mixed $value
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
     *
     * @param mixed $value
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

    public function testSubmitValid(): void
    {
        $form = $this->createForm();
        $form->submit(['plain' => 'secret']);

        self::assertSame('["secret",null]', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
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
        $form = $this->createForm(['hashing' => 'alternative']);
        $form->submit(['plain' => 'secret']);

        self::assertSame('secret', $form->getData());
        self::assertNull($form->getViewData());
        self::assertTrue($form->isSynchronized());
    }

    public function testCustomOptions(): void
    {
        $form = $this->createForm(['password_options' => ['constraints' => new Callback(static function ($value, ExecutionContextInterface $context): void {
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
        $hashing = new class() implements PasswordEncoderInterface {
            public function encodePassword($raw, $salt)
            {
                return (string) json_encode([$raw, $salt]);
            }

            public function isPasswordValid($encoded, $raw, $salt)
            {
                return $encoded === $this->encodePassword($raw, $salt);
            }

            public function needsRehash(string $encoded): bool
            {
                return false;
            }
        };

        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([new HashedPasswordType(new EncoderFactory([
                UserIdentity::class => $hashing,
                'alternative' => ['algorithm' => 'plaintext', 'ignore_case' => false],
            ]))], []),
        ];
    }

    /**
     * @param mixed $data
     */
    private function createForm(array $options = [], $data = null): FormInterface
    {
        return $this->factory->create(HashedPasswordType::class, $data, $options);
    }
}
