<?php
namespace Belt\Aggregator;

/**
 * @author Ramon Kleiss <ramon@apostle.nl>
 */
class TransformingAggregator implements AggregatorInterface
{
    /** @var AggregatorInterface */
    private $aggregator;

    /** @var array */
    private $transformers = array();

    /**
     * Constructor
     *
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
        return $this->aggregator->add(
            $source,
            $type,
            $unique,
            $data,
            $timestamp ?: new \DateTime()
        );
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
        $item = $this->aggregator->get($identifier, $type, $unique);

        if ($item && isset($this->transformers[$item['type']])) {
            $item['data'] = call_user_func_array($this->transformers[$item['type']], [$item]);
        }

        return $item;
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $sources, $limit = 25, $offset = 0)
    {
        $items = $this->aggregator->find($sources, $limit, $offset);

        foreach ($items as &$item) {
            if (isset($this->transformers[$item['type']])) {
                $item['data'] = call_user_func_array($this->transformers[$item['type']], [$item]);
            }
        }

        return $items;
    }

    /**
     * Register a transformer for the given `type`.
     *
     * @param string   $type        The type of items to register the
     *                              transformer for.
     * @param Callable $transformer The transformer to call when an item of the
     *                              given type is requested.
     */
    public function register($type, Callable $transformer)
    {
        $this->transformers[$type] = $transformer;
    }
}
