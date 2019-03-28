<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Adapter;

/**
 * Trait for taggable adapter.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
trait TaggableAdapterTrait
{
    /**
     * Generate tag key.
     *
     * @param string $tag
     *
     * @return string
     */
    private function generateTagKey(string $tag): string
    {
        return TaggableAdapterInterface::TAG_PREFIX.$tag;
    }
}
