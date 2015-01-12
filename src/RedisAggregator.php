<?php
namespace Belt\Aggregator;

use Predis\Client;

/**
 * The `RedisAggregator` is an implementation of the `AggregatorInterface` that
 * uses redis as the storage back-end of the aggregator.
 *
 * @author Ramon Kleiss <ramon@apostle.nl>
 */
class RedisAggregator implements AggregatorInterface
{
    /** @var Client */
    private $redis;

    /**
     * Constructor.
     *
     * @param Client $redis The redis connection to use.
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function add($source, $type, $unique, array $data, \DateTime $timestamp = null)
    {
        $identifier = base64_encode(implode(':', [$source, $type, $unique]));
        $timestamp  = $timestamp ?: new \DateTime();

        $this->redis->hset('aggregator:objects', $identifier, json_encode([
            'source'    => $source,
            'type'      => $type,
            'unique'    => $unique,
            'data'      => $data,
            'timestamp' => $timestamp->format('U')
        ]));

        $this->redis->zadd("aggregator:sources:${source}", $timestamp->format('U'), $identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function has($identifier, $type = null, $unique = null)
    {
        if (!is_null($type) && !is_null($unique)) {
            $identifier = base64_encode(implode(':', [$identifier, $type, $unique]));
        }

        return $this->redis->hexists('aggregator:objects', $identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function get($identifier, $type = null, $unique = null)
    {
        if (!is_null($type) && !is_null($unique)) {
            $identifier = base64_encode(implode(':', [$identifier, $type, $unique]));
        }

        return $this->redis->hget('aggregator:objects', $identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $sources, $limit = 25, $offset = 0)
    {
        call_user_func_array(
            [$this->redis, 'zunionstore'],
            array_merge(
                [($key = sha1(microtime())), count($sources)],
                array_map(function ($source) {
                    return "aggregator:sources:${source}";
                }, $sources)
            )
        );

        $identifiers = $this->redis->zrevrange($key, $offset, ($offset + $limit) - 1);
        $this->redis->del($key);

        $items = $this->redis->hmget('aggregator:objects', $identifiers);

        foreach ($items as &$item) {
            $item              = json_decode($item, true);
            $item['timestamp'] = \DateTime::createFromFormat('U', $item['timestamp']);
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $sources)
    {
        call_user_func_array(
            [$this->redis, 'zunionstore'],
            array_merge(
                [($key = sha1(microtime())), count($sources)],
                array_map(function ($source) {
                    return "aggregator:sources:${source}";
                }, $sources)
            )
        );

        $count = $this->redis->zcard($key);
        $this->redis->del($key);

        return $count;
    }
}
