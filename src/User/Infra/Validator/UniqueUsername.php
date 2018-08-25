<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UniqueUsername extends Constraint
{
    public const IS_NOT_UNIQUE_ERROR = '37c4ba30-07ae-48e5-9767-19764e027346';

    public $message = 'This value is not valid.';
}
