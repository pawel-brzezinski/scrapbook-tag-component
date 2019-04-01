<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\Repository;
use PB\Extension\Scrapbook\Tag\TaggableStoreInterface;

/**
 * Abstract for taggable repository implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
abstract class AbstractTaggableRepository extends Repository implements TaggableRepositoryInterface
{
    /**
     * @var TaggableStoreInterface
     */
    protected $store;

    /**
     * @var array
     */
    protected $unresolvedCurrentTags = [];

    /**
     * @var array
     */
    protected $resolvedCurrentTags = [];
}
