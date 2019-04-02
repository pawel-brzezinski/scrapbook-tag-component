<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\{Item, Repository};

/**
 * Taggable PSR-6 cache item.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggableItem extends Item implements TaggableItemInterface
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * TaggableItem constructor.
     *
     * @param string $key
     * @param Repository $repository
     */
    public function __construct($key, Repository $repository)
    {
        parent::__construct($key, $repository);
        $this->repository->add(self::HASH_TAG_PREFIX.$this->hash, TaggablePoolInterface::KEY_TAGS_PREFIX.$key);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTags(): array
    {
        if (true !== $this->isHit()) {
            return [];
        }

        $currentTags = $this->repository->get(self::HASH_TAG_PREFIX.$this->hash);

        return is_array($currentTags) ? $currentTags : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }
}
