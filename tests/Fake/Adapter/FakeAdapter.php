<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Fake\Adapter;

use PB\Extension\Scrapbook\Tag\Adapter\TaggableAdapterTrait;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FakeAdapter
{
    use TaggableAdapterTrait;

    /**
     * @param string $tag
     *
     * @return string
     */
    public function callGenerateTagKey(string $tag): string
    {
        return $this->generateTagKey($tag);
    }
}
