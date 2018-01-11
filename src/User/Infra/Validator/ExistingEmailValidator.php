<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Validator;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ExistingEmailValidator extends ConstraintValidator
{
    private $emailLookup;

    public function __construct(EmailLookupInterface $emailLookup)
    {
        $this->emailLookup = $emailLookup;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExistingEmail) {
            throw new UnexpectedTypeException($constraint, ExistingEmail::class);
        }

        if (null === $value || '' === ($value = (string) $value)) {
            return;
        }

        if (!($constraint->primary ? $this->emailLookup->existsPrimary($value) : $this->emailLookup->exists($value))) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setInvalidValue($value)
                ->setCode(ExistingEmail::DOES_NOT_EXIST_ERROR)
                ->addViolation();
        }
    }
}
