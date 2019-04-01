<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

/**
 * Interface for taggable PSR-6 repository implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface TaggableRepositoryInterface
{
    /**
     * Get item current tags.
     *
     * @param string $unique
     *
     * @return array
     */
    public function getCurrentTags(string $unique): array;
}
