<?php
namespace Belt\Aggregator\Tests;

use Belt\Aggregator\ArrayAggregator;

class ArrayAggregatorTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setup()
    {
        $this->aggregator = new ArrayAggregator();
    }

    public function testAggregator()
    {
        $identifier = $this->aggregator->add(
            'foo',
            'bar',
            'baz',
            ['key' => 'value']
        );

        $this->assertTrue($this->aggregator->has($identifier));
        $this->assertTrue($this->aggregator->has('foo', 'bar', 'baz'));

        $items = [
            $this->aggregator->get($identifier),
            $this->aggregator->get('foo', 'bar', 'baz')
        ];

        $this->assertEquals($items[0], $items[1]);

        $this->assertEquals('foo', $items[0]['source']);
        $this->assertEquals('bar', $items[0]['type']);
        $this->assertEquals('baz', $items[0]['unique']);
        $this->assertEquals(['key' => 'value'], $items[0]['data']);
        $this->assertEquals(new \DateTime(), $items[0]['timestamp']);
    }

    public function testFind()
    {
        $this->aggregator->add('foo', 'bar', 'baz', ['key' => 'value'], new \DateTime('-5 minutes'));
        $this->aggregator->add('foo', 'baz', 'bar', ['key' => 'value'], new \DateTime('-10 minutes'));

        $items = $this->aggregator->find(['foo'], 25, 0);
        $this->assertEquals(2, $this->aggregator->count(['foo']));

        $this->assertCount(2, $items);
        $this->assertEquals('foo', $items[0]['source']);
        $this->assertEquals('bar', $items[0]['type']);
        $this->assertEquals('baz', $items[0]['unique']);
        $this->assertEquals(['key' => 'value'], $items[0]['data']);
        $this->assertEquals('foo', $items[1]['source']);
        $this->assertEquals('baz', $items[1]['type']);
        $this->assertEquals('bar', $items[1]['unique']);
        $this->assertEquals(['key' => 'value'], $items[1]['data']);
    }

    public function testDoNotThrowErrorForNonExistingSources()
    {
        $this->aggregator->add('foo', 'bar', 'baz', ['key' => 'value'], new \DateTime('-5 minutes'));
        $this->aggregator->add('foo', 'baz', 'bar', ['key' => 'value'], new \DateTime('-10 minutes'));

        $this->aggregator->find(['foo', 'bar', 'baz']);
    }

    public function testReturnFalseIfItemNotInAggregator()
    {
        $this->assertFalse($this->aggregator->has('foo'));
    }

    public function testReturnNullIfItemNotInAggregator()
    {
        $this->assertNull($this->aggregator->get('foo'));
    }
}
