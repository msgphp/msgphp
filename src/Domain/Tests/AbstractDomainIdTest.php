<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainIdInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDomainIdTest extends TestCase
{
    /**
     * @dataProvider provideEmptyIds
     */
    public function testEmptyId(DomainIdInterface $id): void
    {
        $class = get_class($id);

        $this->assertTrue($id->isEmpty());
        $this->assertTrue($id->equals($id));
        $this->assertFalse($id->equals(static::duplicateDomainId($id)));
        $this->assertFalse($id->equals(static::duplicateDomainId($id, true)));
        $this->assertSame('', $id->toString());
        $this->assertSame('', (string) $id);
        $this->assertEquals($id, unserialize(serialize($id)));
        $this->assertEquals($id, $class::fromValue(json_decode(json_encode($id))));
        $this->assertNull(json_decode(json_encode($id)));

        foreach ($this->provideNonEmptyIds() as $otherId) {
            $this->assertFalse($id->equals($otherId[0]));
        }
    }

    /**
     * @dataProvider provideNonEmptyIds
     */
    public function testNonEmptyId(DomainIdInterface $id, $expected): void
    {
        $class = get_class($id);

        $this->assertFalse($id->isEmpty());
        $this->assertTrue($id->equals($id));
        $this->assertTrue($id->equals(static::duplicateDomainId($id)));
        $this->assertFalse($id->equals(static::duplicateDomainId($id, true)));
        $this->assertSame((string) $expected, $id->toString());
        $this->assertSame((string) $expected, (string) $id);
        $this->assertEquals($id, unserialize(serialize($id)));
        $this->assertEquals($id, $class::fromValue(json_decode(json_encode($id))));
        $this->assertSame(json_encode($expected), json_encode($id));
    }

    abstract public function provideEmptyIds(): iterable;

    abstract public function provideNonEmptyIds(): iterable;

    abstract protected static function duplicateDomainId(DomainIdInterface $id, bool $otherType = false): DomainIdInterface;
}
