<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Model;

/**
 * Taggable cache value model.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggableCacheValue
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var array
     */
    private $tags;

    /**
     * TaggableCacheValue constructor.
     *
     * @param $value
     * @param array $tags
     */
    public function __construct($value, array $tags = [])
    {
        $this->value = $value;
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
