<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractCredential
{
    /** @var CredentialInterface */
    private $credential;

    public function getCredential(): CredentialInterface
    {
        return $this->credential;
    }

    private function handleChangeCredentialEvent(ChangeCredentialEvent $event): bool
    {
        $handled = false;

        foreach ($event->fields as $field => $value) {
            $readIsProp = $writeIsProp = false;

            if (!method_exists($this, $writeMethod = 'change'.($ucField = ucfirst($field))) && !method_exists($this, $writeMethod = 'set'.$ucField) && !($writeIsProp = property_exists($this, $field))) {
                throw new \LogicException(sprintf('Unable to handle event "%s", method "%s::change/set%s()" nor property "$%s" does not exists.', get_class($event), get_class($this), $ucField, $field));
            }

            if (!method_exists($this, $readMethod = 'get'.$ucField) && !method_exists($this, $readMethod = 'is'.$ucField) && !method_exists($this, $readMethod = 'has'.$ucField) && !($readIsProp = property_exists($this, $field))) {
                throw new \LogicException(sprintf('Unable to handle event "%s", method "%s::get/is/has%s()" nor property "$%s" does not exists.', get_class($event), get_class($this), $ucField, $field));
            }

            if ($value !== ($readIsProp ? $this->$field : $this->$readMethod())) {
                if ($writeIsProp) {
                    $this->$field = $value;
                } else {
                    $this->$writeMethod($value);
                }

                $handled = true;
            }
        }

        return $handled;
    }
}
