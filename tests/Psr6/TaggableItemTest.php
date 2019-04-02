<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\Repository;
use PB\Extension\Scrapbook\Tag\Psr6\TaggableItem;
use PB\Extension\Scrapbook\Tag\Psr6\TaggableItemInterface;
use PB\Extension\Scrapbook\Tag\Psr6\TaggablePoolInterface;
use PB\Tests\Extension\Scrapbook\Tag\Library\Reflection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggableItemTest extends TestCase
{
    const DEFAULT_KEY = 'foo';

    /** @var ObjectProphecy|Repository */
    private $repoMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->repoMock = $this->prophesize(Repository::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->repoMock = null;
    }

    public function testShouldReturnCurrentCacheItemTagsWhenTagsValueIsAnArray()
    {
        // Given
        $expected = ['tag1', 'tag2'];

        $itemUnderTest = $this->buildItem();
        Reflection::setPropertyValue($itemUnderTest, 'isHit', true);

        // Mock Repository::get()
        $hash = Reflection::getPropertyValue($itemUnderTest, 'hash');
        $keyTagsUnique = TaggableItemInterface::HASH_TAG_PREFIX.$hash;
        $this->repoMock->get($keyTagsUnique)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $itemUnderTest->getCurrentTags();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCurrentCacheItemTagsWhenTagsValueIsNotAnArray()
    {
        // Given
        $expected = [];

        $itemUnderTest = $this->buildItem();
        Reflection::setPropertyValue($itemUnderTest, 'isHit', true);

        // Mock Repository::get()
        $hash = Reflection::getPropertyValue($itemUnderTest, 'hash');
        $keyTagsUnique = TaggableItemInterface::HASH_TAG_PREFIX.$hash;
        $this->repoMock->get($keyTagsUnique)->shouldBeCalledTimes(1)->willReturn('some-not-array-value');
        // End

        // When
        $actual = $itemUnderTest->getCurrentTags();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCurrentCacheItemTagsWhenCacheItemInNotHit()
    {
        // Given
        $expected = [];

        $itemUnderTest = $this->buildItem();
        Reflection::setPropertyValue($itemUnderTest, 'isHit', false);

        // Mock Repository::get()
        $this->repoMock->get(Argument::any())->shouldNotBeCalled();
        // End

        // When
        $actual = $itemUnderTest->getCurrentTags();

        // Then
        $this->assertSame($expected, $actual);
    }


    public function testGetTags()
    {
        // Given
        $expected = ['tag1', 'tag2'];

        $itemUnderTest = $this->buildItem();
        Reflection::setPropertyValue($itemUnderTest, 'tags', $expected);

        // When
        $actual = $itemUnderTest->getTags();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testSetTags()
    {
        // Given
        $expected = ['tag1', 'tag2'];

        $itemUnderTest = $this->buildItem();

        // When
        $actualSetter = $itemUnderTest->setTags($expected);
        $actual = Reflection::getPropertyValue($itemUnderTest, 'tags');

        // Then
        $this->assertSame($itemUnderTest, $actualSetter);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return TaggableItem
     */
    private function buildItem(): TaggableItem
    {
        // Mock TaggableRepositoryInterface::add
        $this->repoMock->add(Argument::type('string'), self::DEFAULT_KEY)->shouldBeCalledTimes(1);
        $this->repoMock->add(Argument::type('string'), TaggablePoolInterface::KEY_TAGS_PREFIX.self::DEFAULT_KEY)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableRepositoryInterface::remove
        $this->repoMock->remove(Argument::type('string'))->shouldBeCalledTimes(1);
        // End

        return new TaggableItem(self::DEFAULT_KEY, $this->repoMock->reveal());
    }
}
