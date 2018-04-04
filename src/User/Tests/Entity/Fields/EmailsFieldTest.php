<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity\Fields;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\Fields\EmailsField;
use MsgPhp\User\Entity\UserEmail;
use PHPUnit\Framework\TestCase;

final class EmailsFieldTest extends TestCase
{
    public function testGetEmails(): void
    {
        $object = $this->getObject($emails = [$this->createMock(UserEmail::class)]);

        $this->assertInstanceOf(DomainCollectionInterface::class, $collection = $object->getEmails());
        $this->assertSame($emails, iterator_to_array($collection));
        $this->assertSame($collection = $this->createMock(DomainCollectionInterface::class), $this->getObject($collection)->getEmails());
    }

    private function getObject($value)
    {
        return new class($value) {
            use EmailsField;

            public function __construct($value)
            {
                $this->emails = $value;
            }
        };
    }
}
