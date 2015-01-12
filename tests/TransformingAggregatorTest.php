<?php
namespace Belt\Aggregator\Tests;

use Belt\Aggregator\ArrayAggregator;
use Belt\Aggregator\TransformingAggregator;

class TransformingAggregatorTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setup()
    {
        $this->aggregator = new TransformingAggregator(new ArrayAggregator());
    }

    public function testTransformSingleItemsRequestedFromAggregator()
    {
        $this->aggregator->register('bar', function ($item) {
            return array_flip($item['data']);
        });

        $this->aggregator->register('baz', function ($item) {
            return substr($item['data']['key'], 1);
        });

        $index = [
            $this->aggregator->add('foo', 'bar', 'baz', ['key' => 'value'], new \DateTime('-5 minutes')),
            $this->aggregator->add('foo', 'baz', 'bar', ['key' => 'value'], new \DateTime('-6 minutes')),
        ];

        $items = [
            $this->aggregator->get($index[0]),
            $this->aggregator->get($index[1]),
        ];

        $this->assertTrue($this->aggregator->has($index[0]));
        $this->assertTrue($this->aggregator->has($index[1]));

        $this->assertEquals(['value' => 'key'], $items[0]['data']);
        $this->assertEquals('alue', $items[1]['data']);
    }

    public function testTransformMultipleItemsRequestedFromAggregator()
    {
        $this->aggregator->register('bar', function ($item) {
            return array_flip($item['data']);
        });

        $this->aggregator->register('baz', function ($item) {
            return substr($item['data']['key'], 1);
        });

        $this->aggregator->add('foo', 'bar', 'baz', ['key' => 'value']);
        $this->aggregator->add('foo', 'baz', 'bar', ['key' => 'value'], new \DateTime('-6 minutes'));

        $items = $this->aggregator->find(['foo']);
        $this->assertEquals(2, $this->aggregator->count(['foo']));

        $this->assertEquals(['value' => 'key'], $items[0]['data']);
        $this->assertEquals('alue', $items[1]['data']);
    }
}
