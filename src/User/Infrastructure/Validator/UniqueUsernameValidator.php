<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Validator;

use MsgPhp\User\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UniqueUsernameValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUsername) {
            throw new UnexpectedTypeException($constraint, UniqueUsername::class);
        }

        if (null === $value || '' === ($value = (string) $value)) {
            return;
        }

        if ($this->repository->usernameExists($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setInvalidValue($value)
                ->setCode(UniqueUsername::IS_NOT_UNIQUE_ERROR)
                ->addViolation()
            ;
        }
    }
}
