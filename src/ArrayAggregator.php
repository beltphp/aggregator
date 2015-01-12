<?php
namespace Belt\Aggregator;

/**
 * The `ArrayAggregator` class is an implementation of the `AggregatorInterface`
 * that uses native PHP arrays to store items.
 *
 * @author Ramon Kleiss <ramon@apostle.nl>
 */
class ArrayAggregator implements AggregatorInterface
{
    /** @var array */
    private $data = array();

    /** @var array */
    private $sources = array();

    /**
     * {@inheritDoc}
     */
    public function add($source, $type, $unique, array $data, \DateTime $timestamp = null)
    {
        $timestamp = $timestamp ?: new \DateTime();

        $this->sources[$source][$timestamp->format('U')] = ($identifier = base64_encode(implode(':', [$source, $type, $unique])));

        $this->data[$identifier]  = [
            'source' => $source,
            'type' => $type,
            'unique' => $unique,
            'data' => $data,
            'timestamp' => $timestamp
        ];

        ksort($this->sources[$source]);

        return $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function has($identifier, $type = null, $unique = null)
    {
        if (!is_null($identifier) && !is_null($type) && !is_null($unique)) {
            $identifier = base64_encode(implode(':', [$identifier, $type, $unique]));
        }

        return isset($this->data[$identifier]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($identifier, $type = null, $unique = null)
    {
        if (!is_null($identifier) && !is_null($type) && !is_null($unique)) {
            $identifier = base64_encode(implode(':', [$identifier, $type, $unique]));
        }

        return $this->has($identifier) ? $this->data[$identifier] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $sources, $limit = 25, $offset = 0)
    {
        $items   = array();
        $sources = array_filter($sources, function ($source) {
            return isset($this->sources[$source]);
        });

        array_walk($sources, function ($source) use (&$items) {
            $items = array_merge($items, $this->sources[$source]);
        });

        krsort($items);

        return array_values(array_map(function ($identifier) {
            return $this->get($identifier);
        }, array_slice($items, $offset, $limit)));
    }
}
