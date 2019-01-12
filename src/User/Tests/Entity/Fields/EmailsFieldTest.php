<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity\Fields;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\Fields\EmailsField;
use MsgPhp\User\Entity\UserEmail;
use PHPUnit\Framework\TestCase;

final class EmailsFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($emails = [$this->createMock(UserEmail::class)]);

        self::assertInstanceOf(DomainCollectionInterface::class, $collection = $object->getEmails());
        self::assertSame($emails, iterator_to_array($collection));
        self::assertSame($collection = $this->createMock(DomainCollectionInterface::class), $this->getObject($collection)->getEmails());
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
