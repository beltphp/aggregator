<?php
namespace Belt\Aggregator;

/**
 * The `ProcessingAggregator` is an implementation of the `AggregatorInterface`
 * that adds the ability to post-process data items.
 *
 * @author Ramon Kleiss <ramon@apostle.nl>
 */
class ProcessingAggregator implements AggregatorInterface
{
    /** @var AggregatorInterface */
    private $aggregator;

    /** @var array */
    private $processors = array();

    /**
     * Constructor.
     *
     * @param AggregatorInterface $aggregator The aggregator to wrap.
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
            $timestamp ?: new \DateTime(),
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
        $item = $this->aggregator->get($identifier, $type, $unique)

        if ($item !== null && isset($this->processors[$item['type']])) {
            call_user_func_array($this->processors[$item['type']], [ $item ]);
        }

        return $item;
    }

    /**
     * Register a post-processor for the given `type`.
     *
     * A post-processor is called after a data item is retrieve from the
     * aggregator but before it is returned to the user.
     *
     * __Note__: Only one post-processor per type can be registered.
     *
     * @param string   $type      The type to register the pre-processor for.
     * @param Callable $processor The actual post-processor to call after a
     *                            data item with the given `type` is retrieved
     *                            to the aggregator.
     */
    public function register($type, Callable $processor)
    {
        $this->processors[$type] = $processor;
    }
}
