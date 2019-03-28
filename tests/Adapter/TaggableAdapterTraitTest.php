<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Adapter;

use PB\Extension\Scrapbook\Tag\Adapter\TaggableAdapterInterface;
use PB\Tests\Extension\Scrapbook\Tag\Fake\Adapter\FakeAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggableAdapterTrait extends TestCase
{
    public function generateTagKeyDataProvider(): array
    {
        return [
            [TaggableAdapterInterface::TAG_PREFIX.'foo', 'foo'],
            [TaggableAdapterInterface::TAG_PREFIX.'bar', 'bar'],
        ];
    }

    /**
     * @dataProvider generateTagKeyDataProvider
     *
     * @param string $expected
     * @param string $tag
     */
    public function testGenerateTagKey(string $expected, string $tag)
    {
        // When
        $actual = $this->buildAdapter()->callGenerateTagKey($tag);

        // Then
        $this->assertSame($expected, $actual);
    }

    private function buildAdapter(): FakeAdapter
    {
        return new FakeAdapter();
    }
}
