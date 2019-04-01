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
    /**
     * Invalidate tags.
     *
     * @param array $tags
     *
     * @return array
     */
    public function invalidateTags(array $tags): array;
}
