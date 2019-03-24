<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Model;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\Model\EmailsField;
use MsgPhp\User\UserEmail;
use PHPUnit\Framework\TestCase;

final class EmailsFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($emails = [$this->createMock(UserEmail::class)]);

        self::assertInstanceOf(DomainCollection::class, $collection = $object->getEmails());
        self::assertSame($emails, iterator_to_array($collection));
        self::assertNotSame($collection = $this->createMock(DomainCollection::class), $this->getObject($collection)->getEmails());
    }

    /**
     * @return object
     */
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
