<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag;

use MatthiasMullie\Scrapbook\KeyValueStore;
use PB\Extension\Scrapbook\Tag\Model\TaggableCacheValue;

/**
 * Interface for taggable key-value store implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface TaggableStoreInterface extends KeyValueStore
{
    /**
     * Get plain cache value (it may be TaggableCacheValue).
     *
     * @param string $key
     * @param mixed $token
     *
     * @return mixed|TaggableCacheValue
     */
    public function getPlain(string $key, &$token = null);

    /**
     * Get plain cache for multiple keys (it may be collection of TaggableCacheValue).
     *
     * @param array $keys
     * @param array $tokens
     *
     * @return array
     */
    public function getMultiPlain(array $keys, &$tokens = null);

    /**
     * Set cache value with tags.
     *
     * @param string $key
     * @param $value
     * @param array $tags
     * @param int $expire
     *
     * @return bool
     */
    public function setWithTags(string $key, $value, array $tags = [], int $expire = 0): bool;

    /**
     * Set multi cache values with tags.
     *
     * @param array $items
     * @param array $tags
     * @param int $expire
     *
     * @return bool[]
     */
    public function setMultiWithTags(array $items, array $tags = [], int $expire = 0): array;

    /**
     * Invalidate tags.
     *
     * @param array $tags
     *
     * @return array
     */
    public function invalidateTags(array $tags): array;
}
