<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ExistingUsername extends Constraint
{
    public const DOES_NOT_EXIST_ERROR = '4a8b28f7-a2b5-4435-9dd8-3be5188d23f0';

    public $message = 'This value is not a valid username.';
}
