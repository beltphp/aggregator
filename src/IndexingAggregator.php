<?php
namespace Belt\Aggregator;

/**
 * The `IndexingAggregator` is an implementation of the `AggregatorInterface`
 * that wraps another aggregator and allows users to add indexers that are
 * called after the data is added to the aggregator.
 *
 * @author Ramon Kleiss <ramon@apostle.nl>
 */
class IndexingAggregator implements AggregatorInterface
{
    /** @var AggregatorInterface */
    private $aggregator;

    /** @var array */
    private $indexers = array();

    /**
     * @param AggregatorInterface $aggregator
     */
    public function __construct(AggregatorInterface $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * {@inheritDoc}
     */
    public function add($source, $type, $unique, array $data, \DateTime $timestamp = null)
    {
        $item = [
            'source'    => $source,
            'type'      => $type,
            'unique'    => $unique,
            'data'      => $data,
            'timestamp' => $timestamp ?: new \DateTime(),
        ];

        $identifier = $this->aggregator->add(
            $source,
            $type,
            $unique,
            $data,
            $timestamp ?: new \DateTime()
        );

        if (isset($this->indexers[$type])) {
            array_walk($this->indexers[$type], function ($indexer) use ($item) {
                call_user_func_array($indexer, [ $item ]);
            });
        }

        return $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function has($identifier, $type = null, $unique = null)
    {
        return $this->aggregator->has($identifier, $type, $unique);
    }

    /**
     * {@inheritDoc}
     */
    public function get($identifier, $type = null, $unique = null)
    {
        return $this->aggregator->get($identifier, $type, $unique);
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $sources, $limit = 25, $offset = 0)
    {
        return $this->aggregator->find($sources, $limit, $offset);
    }

    /**
     * @param string   $type
     * @param Callable $indexer
     */
    public function register($type, Callable $indexer)
    {
        $this->indexers[$type][] = $indexer;
    }
}
