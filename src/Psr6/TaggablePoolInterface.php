<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Interface for taggable PSR-6 cache pool implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface TaggablePoolInterface extends CacheItemPoolInterface
{
    const TAG_PREFIX = '[tag]';
    const KEY_TAGS_PREFIX = '[tags]';

    /**
     * Invalidate tags.
     *
     * @param array $tags
     *
     * @return bool
     */
    public function invalidateTags(array $tags): bool;
}
