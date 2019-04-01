<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\Item;

/**
 * Taggable PSR-6 cache item.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggableItem extends Item implements TaggableItemInterface
{
    /**
     * @var TaggableRepositoryInterface
     */
    protected $repository;

    /**
     * @var array
     */
    private $currentTags = null;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * TaggableItem constructor.
     *
     * @param string $key
     * @param AbstractTaggableRepository $repository
     */
    public function __construct($key, AbstractTaggableRepository $repository)
    {
        parent::__construct($key, $repository);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTags(): array
    {
        if (null !== $this->currentTags[$this->hash]) {
            return $this->currentTags[$this->hash];
        }

        if (false === $this->isHit()) {
            return [];
        }

        return $this->repository->getCurrentTags($this->hash);
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
