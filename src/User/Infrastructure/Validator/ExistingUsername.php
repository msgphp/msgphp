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
final class ExistingUsername extends Constraint
{
    public const DOES_NOT_EXIST_ERROR = '4a8b28f7-a2b5-4435-9dd8-3be5188d23f0';

    /**
     * @var string
     */
    public $message = 'This value is not valid.';

    /**
     * @var string[]
     */
    protected static $errorNames = [
        self::DOES_NOT_EXIST_ERROR => 'DOES_NOT_EXIST_ERROR',
    ];
}
