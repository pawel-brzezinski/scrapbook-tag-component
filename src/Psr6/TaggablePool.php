<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\InvalidArgumentException;
use MatthiasMullie\Scrapbook\Psr6\Pool;
use PB\Extension\Scrapbook\Tag\TaggableStoreInterface;
use Psr\Cache\CacheItemInterface;

/**
 * Taggable PSR-6 cache pool.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggablePool extends Pool implements TaggablePoolInterface
{
    /**
     * @var TaggableRepositoryInterface
     */
    protected $repository;

    /**
     * TaggablePool constructor.
     *
     * @param TaggableStoreInterface $store
     */
    public function __construct(TaggableStoreInterface $store)
    {
        parent::__construct($store);
        $this->repository = new TaggableRepository($store);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key): TaggableItemInterface
    {
        $item = parent::getItem($key);

        return new TaggableItem($item->getKey(), $this->repository);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if (!$item instanceof TaggableItemInterface) {
            $message = sprintf('%s can only save %s objects', self::class, TaggableItemInterface::class);
            throw new InvalidArgumentException($message);
        }

        if (true !== parent::save($item)) {
            return false;
        }

        $this->store()->addKeyToTags($item->getKey(), $item->getTags());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$item instanceof TaggableItemInterface) {
            $message = sprintf('%s can only save %s objects', self::class, TaggableItemInterface::class);
            throw new InvalidArgumentException($message);
        }

        return parent::saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $tags = [];

        /**
         * @var string $key
         * @var TaggableItemInterface $item
         */
        foreach ($this->deferred as $key => $item) {
            foreach ($item->getTags() as $tag) {
                $tags[$tag][] = $key;
            }
        }

        if (false === parent::commit()) {
            $this->deferred = [];
            return false;
        }

        if (!empty($tags)) {
            $this->store()->addKeysToTags($tags);
        }

        $this->deferred = [];

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateTags(array $tags): array
    {
        return $this->store()->invalidateTags($tags);
    }

    /**
     * Get store.
     *
     * @return TaggableStoreInterface
     */
    private function store(): TaggableStoreInterface
    {
        return $this->store;
    }
}
