<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Adapter;

use MatthiasMullie\Scrapbook\KeyValueStore;

/**
 * Interface for taggable adapter implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface TaggableAdapterInterface extends KeyValueStore
{
    const TAG_PREFIX = '[tag]';

    /**
     * Add key to tags.
     *
     * @param string $key
     * @param array $tags
     *
     * @return int
     */
    public function addKeyToTags(string $key, array $tags): int;

    /**
     * Add keys to tags.
     *
     * @param array $tags       [tag => [keys]]
     *
     * @return int
     */
    public function addKeysToTags(array $tags): int;

    /**
     * Remove cache key from tags.
     *
     * @param string $key
     * @param array $tags
     *
     * @return int
     */
    public function removeKeyFromTags(string $key, array $tags): int;

    /**
     * Remove cache keys from tags.
     *
     * @param array $tags       [tag => [keys]]
     *
     * @return int
     */
    public function removeKeysFromTags(array $tags): int;

    /**
     * Gets tags cache keys.
     *
     * @param array $tags
     *
     * @return array
     */
    public function getTagsCacheKeys(array $tags): array;
}
