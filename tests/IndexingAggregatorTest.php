<?php
namespace Belt\Aggregator;

use Belt\Aggregator\ArrayAggregator;
use Belt\Aggregator\IndexingAggregator;

class IndexingAggregatorTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setup()
    {
        $this->aggregator = new IndexingAggregator(new ArrayAggregator());
    }

    public function testIndexDifferentTypesWhenItemsAreAdded()
    {
        $count = ['bar' => 0, 'baz' => 0];

        $this->aggregator->register('bar', function () use (&$count) {
            $count['bar']++;
        });

        $this->aggregator->register('baz', function () use (&$count) {
            $count['baz']++;
        });

        $index = $this->aggregator->add('foo', 'bar', 'foo', ['key' => 'value'], new \DateTime('-7 minutes'));
        $index = $this->aggregator->add('foo', 'bar', 'bar', ['key' => 'value'], new \DateTime('-6 minutes'));
        $index = $this->aggregator->add('foo', 'bar', 'baz', ['key' => 'value'], new \DateTime('-5 minutes'));

        $index = $this->aggregator->add('foo', 'baz', 'foo', ['key' => 'value'], new \DateTime('-2 minutes'));
        $index = $this->aggregator->add('foo', 'baz', 'bar', ['key' => 'value'], new \DateTime('-1 minutes'));
        $index = $this->aggregator->add('foo', 'baz', 'baz', ['key' => 'value']);

        $this->assertTrue($this->aggregator->has($index));
        $this->assertInternalType('array', $this->aggregator->get($index));
        $this->assertCount(6, $this->aggregator->find(['foo']));

        $this->assertEquals(3, $count['bar']);
        $this->assertEquals(3, $count['baz']);
    }
}
