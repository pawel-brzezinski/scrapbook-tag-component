<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\Item;
use Psr\Cache\CacheItemInterface;

/**
 * Interface for taggable PSR-6 cache item implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface TaggableItemInterface extends CacheItemInterface
{
    /**
     * Get current tags.
     *
     * @return array
     */
    public function getCurrentTags(): array;

    /**
     * Get tags.
     *
     * @return array
     */
    public function getTags(): array;

    /**
     * Set tags.
     *
     * @param array $tags
     *
     * @return TaggableItemInterface
     */
    public function setTags(array $tags);
}
