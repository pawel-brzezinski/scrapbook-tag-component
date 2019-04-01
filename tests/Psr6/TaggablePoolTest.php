<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\InvalidArgumentException;
use MatthiasMullie\Scrapbook\Psr6\Item;
use PB\Extension\Scrapbook\Tag\Psr6\{AbstractTaggableRepository, TaggableItem, TaggableItemInterface, TaggablePool};
use PB\Extension\Scrapbook\Tag\TaggableStoreInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggablePoolTest extends TestCase
{
    /** @var ObjectProphecy|TaggableStoreInterface */
    private $storeMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->storeMock = $this->prophesize(TaggableStoreInterface::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->storeMock = null;
    }

    public function testGetItem()
    {
        // Given
        $key = 'foo';

        // When
        $actual = $this->buildPool()->getItem($key);

        // Then
        $this->assertInstanceOf(TaggableItem::class, $actual);
        $this->assertSame($key, $actual->getKey());
    }

    public function testShouldReturnTrueWhenSaveCacheItemIsTaggableAndSaveItemToStoreReturnTrueAndSaveItemTagsReturnTrue()
    {
        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem = new TaggableItem('foo', $repoMock->reveal());
        $cacheItem->set('Lorem Ipsum');
        $cacheItem->setTags(['tag1', 'tag2']);

        // Mock TaggableStoreInterface::set()
        $this->storeMock
            ->set($cacheItem->getKey(), $cacheItem->get(), Argument::type('int'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;
        // End

        // Mock TaggableStoreInterface::set()
        $this->storeMock->addKeyToTags($cacheItem->getKey(), $cacheItem->getTags())->shouldBeCalledTimes(1)->willReturn(true);
        // End

        // When
        $actual = $this->buildPool()->save($cacheItem);

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldReturnFalseWhenSaveCacheItemIsTaggableAndSaveItemToStoreReturnFalse()
    {
        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem = new TaggableItem('foo', $repoMock->reveal());
        $cacheItem->set('Lorem Ipsum');
        $cacheItem->setTags(['tag1', 'tag2']);

        // Mock TaggableStoreInterface::set()
        $this->storeMock
            ->set($cacheItem->getKey(), $cacheItem->get(), Argument::type('int'))
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;
        // End

        // Mock TaggableStoreInterface::set()
        $this->storeMock->addKeyToTags(Argument::any(), Argument::any())->shouldNotBeCalled();
        // End

        // When
        $actual = $this->buildPool()->save($cacheItem);

        // Then
        $this->assertFalse($actual);
    }

    public function testShouldThrowInvalidArgumentExceptionWhenSavedCacheItemIsNotTaggableItemInstance()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('%s can only save %s objects', TaggablePool::class, TaggableItemInterface::class)
        );

        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem = new Item('foo', $repoMock->reveal());
        $cacheItem->set('Lorem Ipsum');

        // When
        $this->buildPool()->save($cacheItem);
    }

    public function testShouldReturnTrueWhenSaveDeferredCacheItemIsInstanceOfTaggableItemInterfaceAndCommitToStoreReturnTrue()
    {
        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem = new TaggableItem('foo', $repoMock->reveal());
        $cacheItem->set('Lorem Ipsum');
        $cacheItem->setTags(['tag1', 'tag2']);

        // Mock TaggableStoreInterface::setMulti()
        $items = ['foo' => 'Lorem Ipsum'];
        $this->storeMock->setMulti($items, 0)->shouldBeCalledTimes(1)->willReturn([true]);
        // End

        // Mock TaggableStoreInterface::addKeysToTags()
        $tags = ['tag1' => ['foo'], 'tag2' => ['foo']];
        $this->storeMock->addKeysToTags($tags)->shouldBeCalledTimes(1)->wilLReturn(true);
        // End

        // When
        $actual = $this->buildPool()->saveDeferred($cacheItem);

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldThrowInvalidArgumentExceptionWhenSavedDeferredCacheItemIsNotTaggableItemInstance()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('%s can only save %s objects', TaggablePool::class, TaggableItemInterface::class)
        );

        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem = new Item('foo', $repoMock->reveal());
        $cacheItem->set('Lorem Ipsum');

        // When
        $this->buildPool()->saveDeferred($cacheItem);
    }

    public function testShouldReturnTrueWhenCommitDeferredItemsWillSuccessfulSaveAllItemsToStoreAndSuccessfulStoreTags()
    {
        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem1 = new TaggableItem('key1', $repoMock->reveal());
        $cacheItem1->set('Value 1')->setTags(['tag1', 'tag2']);
        $cacheItem2 = new TaggableItem('key2', $repoMock->reveal());
        $cacheItem2->set('Value 2')->setTags(['tag2']);
        $cacheItem3 = new TaggableItem('key3', $repoMock->reveal());
        $cacheItem3->set('Value 3')->setTags(['tag1', 'tag3']);

        // Mock TaggableStoreInterface::setMulti()
        $deferred = ['key1' => 'Value 1', 'key2' => 'Value 2', 'key3' => 'Value 3'];
        $this->storeMock->setMulti($deferred, 0)->shouldBeCalledTimes(1)->willReturn([true, true, true]);
        // End

        // Mock TaggableStoreInterface::addKeysToTags()
        $tags = [
            'tag1' => ['key1', 'key3'],
            'tag2' => ['key1', 'key2'],
            'tag3' => ['key3'],
        ];
        $this->storeMock->addKeysToTags($tags)->shouldBeCalledTimes(1)->willReturn(true);
        // End

        $poolUnderTest = $this->buildPool();
        $poolUnderTest->saveDeferred($cacheItem1);
        $poolUnderTest->saveDeferred($cacheItem2);
        $poolUnderTest->saveDeferred($cacheItem3);

        // When
        $actual = $poolUnderTest->commit();

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldReturnFalseWhenCommitDeferredItemsWillNotSuccessfulSaveAllItemsToStore()
    {
        // Given
        $repoMock = $this->prophesize(AbstractTaggableRepository::class);
        $cacheItem1 = new TaggableItem('key1', $repoMock->reveal());
        $cacheItem1->set('Value 1')->setTags(['tag1', 'tag2']);
        $cacheItem2 = new TaggableItem('key2', $repoMock->reveal());
        $cacheItem2->set('Value 2')->setTags(['tag2']);
        $cacheItem3 = new TaggableItem('key3', $repoMock->reveal());
        $cacheItem3->set('Value 3')->setTags(['tag1', 'tag3']);

        // Mock TaggableStoreInterface::setMulti()
        $deferred = ['key1' => 'Value 1', 'key2' => 'Value 2', 'key3' => 'Value 3'];
        $this->storeMock->setMulti($deferred, 0)->shouldBeCalledTimes(1)->willReturn([true, false, true]);
        // End

        // Mock TaggableStoreInterface::addKeysToTags()
        $this->storeMock->addKeysToTags(Argument::any())->shouldNotBeCalled();
        // End

        $poolUnderTest = $this->buildPool();
        $poolUnderTest->saveDeferred($cacheItem1);
        $poolUnderTest->saveDeferred($cacheItem2);
        $poolUnderTest->saveDeferred($cacheItem3);

        // When
        $actual = $poolUnderTest->commit();

        // Then
        $this->assertFalse($actual);
    }

    public function testInvalidateTags()
    {
        // Given
        $tags = ['tag1', 'tag2'];
        $expected = [true, true];

        // Mock TaggableStoreInterface::invalidateTags()
        $this->storeMock->invalidateTags($tags)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildPool()->invalidateTags($tags);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @return TaggablePool
     */
    private function buildPool(): TaggablePool
    {
        return new TaggablePool($this->storeMock->reveal());
    }
}
