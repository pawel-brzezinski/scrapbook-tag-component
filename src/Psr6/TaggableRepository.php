<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use PB\Extension\Scrapbook\Tag\Model\TaggableCacheValue;

/**
 * Taggable PSR-6 repository.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggableRepository extends AbstractTaggableRepository
{
    /**
     * {@inheritDoc}
     */
    public function add($unique, $key)
    {
        parent::add($unique, $key);
        $this->unresolvedCurrentTags[$unique] = $key;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($unique)
    {
        parent::remove($unique);
        unset($this->unresolvedCurrentTags[$unique], $this->resolvedCurrentTags[$unique]);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentTags(string $unique): array
    {
        if (false === $this->exists($unique)) {
            return [];
        }

        if (array_key_exists($unique, $this->resolvedCurrentTags)) {
            return $this->resolvedCurrentTags[$unique];
        }

        $this->resolveTaggableCacheItems();

        return $this->resolvedCurrentTags[$unique] ?? [];
    }

    /**
     * Resolve TaggableCacheItems.
     */
    private function resolveTaggableCacheItems(): void
    {
        $keys = array_unique(array_values($this->unresolvedCurrentTags));
        $values = $this->store->getMultiPlain($keys);

        foreach ($this->unresolvedCurrentTags as $hash => $key) {
            $cacheItem = $values[$key] ?? null;

            if (null === $cacheItem || !$cacheItem instanceof TaggableCacheValue) {
                $this->resolvedCurrentTags[$hash] = [];
                continue;
            }

            $this->resolvedCurrentTags[$hash] = $cacheItem->getTags();
        }

        $this->unresolvedCurrentTags = [];
    }
}
