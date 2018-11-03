<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Config;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\PrototypedArrayNode;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassMappingNode extends PrototypedArrayNode
{
    private $hints = [];

    public function setAllowEmptyValue($allowEmptyValue): void
    {
        $this->setMinNumberOfElements($allowEmptyValue ? 0 : 1);
    }

    public function setHints(array $hints): void
    {
        $this->hints = $hints;
    }

    protected function validateType($value): void
    {
        parent::validateType($value);

        if (!\is_array($value)) {
            throw new \UnexpectedValueException(sprintf('Expected configuration value to be type array, got "%s".', \gettype($value)));
        }

        foreach ($value as $k => $v) {
            if (class_exists($k) || interface_exists($k, false)) {
                continue;
            }

            $e = new InvalidConfigurationException(sprintf('A class or interface named "%s" does not exists at path "%s".%s', $k, $this->getPath(), isset($this->hints[$k]) ? ' '.$this->hints[$k] : ''));
            $e->setPath($this->getPath());
            if ($hint = $this->getInfo()) {
                $e->addHint($hint);
            }

            throw $e;
        }
    }
}
