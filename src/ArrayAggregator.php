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
        $this->sources[$source][] = ($identifier = base64_encode(implode(':', [$source, $type, $unique])));

        $this->data[$identifier]  = [
            'source' => $source,
            'type' => $type,
            'unique' => $unique,
            'data' => $data,
            'timestamp' => $timestamp ?: new \DateTime(),
        ];

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
}
