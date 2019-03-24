<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Validator;

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

    /**
     * @var string
     */
    public $message = 'This value is not valid.';

    /**
     * @var string[]
     */
    protected static $errorNames = [
        self::IS_NOT_UNIQUE_ERROR => 'IS_NOT_UNIQUE_ERROR',
    ];
}
