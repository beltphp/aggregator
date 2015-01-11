<?php
namespace Belt\Aggregator;

/**
 * The `AggregatorInterface` defines the interface aggregator implementations
 * must implement.
 *
 * @author Ramon Kleiss <ramon@apostle.nl>
 */
interface AggregatorInterface
{
    /**
     * Add a data item to the aggregator.
     *
     * @param string   $source    The source of the data item (if the item comes
     *                            from Twitter, this can be `twitter`).
     * @param string   $type      The type of the data item (if the item is an
     *                            article from a RSS feed, this can be `rss`).
     * @param string   $unique    The unique value of the item, this is used to
     *                            generate an aggregator item identifier.
     * @param array    $data      The actual data of the item.
     * @param DateTime $timestamp (optional) The timestamp of the data item (if
     *                            no value is given, the current time is used).
     *
     * @return string             The aggregator item identifier that can be
     *                            used to retrieve the item from the aggregator.
     */
    public function add($source, $type, $unique, array $data, \DateTime $timestamp = null);

    /**
     * Check if a data item is present in the aggregator either by the
     * `source`, `type` and `unique` combination or the actual aggregator
     * item identifier.
     *
     * @param string $identifier Either the identifier of the item to check or
     *                           the source of the item.
     * @param string $type       (optional) If the source of the item is given
     *                           for the `identifier` parameter, this value
     *                           should be the type of the object to check the
     *                           existence of.
     * @param string $unique     (optional) If the source of the item is given
     *                           for the `identifier` parameter, this value
     *                           should be the the unique value for the object
     *                           to check the existence of.
     *
     * @return Boolean           Returns `true` if the object is contained by
     *                           the aggregator, `false` otherwise.
     */
    public function has($identifier, $type = null, $unique = null);

    /**
     * Retrieve a data item from the aggregator either by its aggregator item
     * identifier or the `source`, `type` and `unique` combination.
     *
     * @param string $identifier Either the identifier of the item to retrieve
     *                           or the source of the item.
     * @param string $type       (optional) If the source of the item is given
     *                           for the `identifier` parameter, this value
     *                           should be the type of the object to retrieve.
     * @param string $unique     (optional) If the source of the item is given
     *                           for the `identifier` parameter, this value
     *                           should be the the unique value for the object
     *                           to retrieve.
     *
     * @return null|array{
     *      @var string   $source    The source of the item.
     *      @var string   $type      The type of the item.
     *      @var string   $unique    The unique value for the item.
     *      @var array    $data      The actual item data.
     *      @var DateTime $timestamp The timestamp for the item.
     * }
     */
    public function get($identifier, $type = null, $unique = null);

    /**
     * Get an array of data items contained by the given `sources`.
     *
     * @param array   $sources
     * @param integer $limit
     * @param integer $offset
     */
    public function find(array $sources, $limit, $offset);
}
