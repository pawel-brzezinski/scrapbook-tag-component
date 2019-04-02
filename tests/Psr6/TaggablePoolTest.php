<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\KeyValueStore;
use MatthiasMullie\Scrapbook\Psr6\{InvalidArgumentException, Item, Repository};
use PB\Extension\Scrapbook\Tag\Psr6\{
    TaggableItem,
    TaggablePool,
    TaggablePoolInterface
};
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggablePoolTest extends TestCase
{
    /** @var ObjectProphecy|KeyValueStore */
    private $storeMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->storeMock = $this->prophesize(KeyValueStore::class);
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

    public function testDeleteItem()
    {
        // Given
        $key = 'foo';
        $keyTagsKey = TaggablePoolInterface::KEY_TAGS_PREFIX.$key;

        // Mock KeyValueStore::deleteMulti()
        $this->storeMock->deleteMulti([$key, $keyTagsKey])->shouldBeCalledTimes(1)->willReturn([true, true]);
        // End

        // When
        $actual = $this->buildPool()->deleteItem($key);

        // Then
        $this->assertTrue($actual);
    }

    public function testDeleteItemWhenKeyIsInvalid()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);

        // Given
        $key = 123;

        // Mock KeyValueStore::deleteMulti()
        $this->storeMock->deleteMulti(Argument::any())->shouldNotBeCalled();
        // End

        // When
        $this->buildPool()->deleteItem($key);
    }

    public function testDeleteItems()
    {
        // Given
        $keys = ['foo', 'bar'];
        $keyTagsKeys = [TaggablePoolInterface::KEY_TAGS_PREFIX.$keys[0], TaggablePoolInterface::KEY_TAGS_PREFIX.$keys[1]];

        // Mock KeyValueStore::deleteMulti()
        $this->storeMock->deleteMulti(array_merge($keys, $keyTagsKeys))->shouldBeCalledTimes(1)->willReturn([true, true, true, true]);
        // End

        // When
        $actual = $this->buildPool()->deleteItems($keys);

        // Then
        $this->assertTrue($actual);
    }

    public function testDeleteItemsWhenOneOfKeysIsInvalid()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);

        // Given
        $keys = ['foo', 123];

        // Mock KeyValueStore::deleteMulti()
        $this->storeMock->deleteMulti(Argument::any())->shouldNotBeCalled();
        // End

        // When
        $this->buildPool()->deleteItems($keys);
    }

    public function testSave()
    {
        // Given
        $repoMock = $this->prophesize(Repository::class);
        $item = new TaggableItem('foo', $repoMock->reveal());
        $item->set('Value');
        $item->setTags(['tag1', 'tag2', 'tag3']);

        $keyTagsKey = TaggablePoolInterface::KEY_TAGS_PREFIX.'foo';
        $tag1Key = TaggablePoolInterface::TAG_PREFIX.'tag1';
        $tag2Key = TaggablePoolInterface::TAG_PREFIX.'tag2';
        $tag3Key = TaggablePoolInterface::TAG_PREFIX.'tag3';
        $tagXKey = TaggablePoolInterface::TAG_PREFIX.'tagX';

        // Mock KeyValueStore::get()
        $this->storeMock->get($keyTagsKey)->shouldBeCalledTimes(1)->willReturn(['tagX', 'tag2']);
        // End

        // Mock KeyValueStore::getMulti()
        $tagValues = [$tag1Key, $tag2Key, $tag3Key, $tagXKey];
        $this->storeMock->getMulti($tagValues)->shouldBeCalledTimes(1)->willReturn([
            $tag1Key => false,
            $tag2Key => ['foo', 'bar'],
            $tag3Key => ['key1', 'key2'],
            $tagXKey => ['foo', 'lorem'],
        ]);
        // End

        // Mock KeyValueStore::setMulti()
        $deferred = [
            $keyTagsKey => ['tag1', 'tag2', 'tag3'],
            $tagXKey => ['lorem'],
            $tag1Key => ['foo'],
            $tag2Key => ['foo', 'bar'],
            $tag3Key => ['key1', 'key2', 'foo'],
            'foo' => 'Value',
        ];
        $this->storeMock->setMulti($deferred, 0)->shouldBeCalledTimes(1)->willReturn([true, true]);
        // End

        // When
        $actual = $this->buildPool()->save($item);

        // Then
        $this->assertTrue($actual);
    }

    public function testSaveDeferred()
    {
        // Given
        $repoMock = $this->prophesize(Repository::class);
        $item = new TaggableItem('foo', $repoMock->reveal());
        $item->set('Value');
        $item->setTags(['tag1', 'tag2', 'tag3']);

        $keyTagsKey = TaggablePoolInterface::KEY_TAGS_PREFIX.'foo';
        $tag1Key = TaggablePoolInterface::TAG_PREFIX.'tag1';
        $tag2Key = TaggablePoolInterface::TAG_PREFIX.'tag2';
        $tag3Key = TaggablePoolInterface::TAG_PREFIX.'tag3';
        $tagXKey = TaggablePoolInterface::TAG_PREFIX.'tagX';

        // Mock KeyValueStore::get()
        $this->storeMock->get($keyTagsKey)->shouldBeCalledTimes(1)->willReturn(['tagX', 'tag2']);
        // End

        // Mock KeyValueStore::getMulti()
        $tagValues = [$tag1Key, $tag2Key, $tag3Key, $tagXKey];
        $this->storeMock->getMulti($tagValues)->shouldBeCalledTimes(1)->willReturn([
            $tag1Key => false,
            $tag2Key => ['foo', 'bar'],
            $tag3Key => ['key1', 'key2'],
            $tagXKey => ['foo', 'lorem'],
        ]);
        // End

        // Mock KeyValueStore::setMulti()
        $deferred = [
            $keyTagsKey => ['tag1', 'tag2', 'tag3'],
            $tagXKey => ['lorem'],
            $tag1Key => ['foo'],
            $tag2Key => ['foo', 'bar'],
            $tag3Key => ['key1', 'key2', 'foo'],
            'foo' => 'Value',
        ];
        $this->storeMock->setMulti($deferred, 0)->shouldBeCalledTimes(1)->willReturn([true, true]);
        // End

        // When
        $actual = $this->buildPool()->saveDeferred($item);

        // Then
        $this->assertTrue($actual);
    }

    public function testSaveDeferredWhenItemIsNotTaggable()
    {
        // Given
        $repoMock = $this->prophesize(Repository::class);
        $item = new Item('foo', $repoMock->reveal());
        $item->set('Value');

        // Mock KeyValueStore::setMulti()
        $deferred = ['foo' => 'Value'];
        $this->storeMock->setMulti($deferred, 0)->shouldBeCalledTimes(1)->willReturn([true, true]);
        // End

        // When
        $actual = $this->buildPool()->saveDeferred($item);

        // Then
        $this->assertTrue($actual);
    }

    public function testInvalidateTags()
    {
        // Given
        $tags = ['tag1', 'tag2', 'tag3'];

        // Mock KeyValueStore::getMulti()
        $tagsKeys = [
            TaggablePoolInterface::TAG_PREFIX.$tags[0],
            TaggablePoolInterface::TAG_PREFIX.$tags[1],
            TaggablePoolInterface::TAG_PREFIX.$tags[2],
        ];
        $tagsValues = [
            TaggablePoolInterface::TAG_PREFIX.$tags[0] => ['key1', 'key2'],
            TaggablePoolInterface::TAG_PREFIX.$tags[1] => false,
            TaggablePoolInterface::TAG_PREFIX.$tags[2] => ['key1', 'key3'],
        ];

        $this->storeMock->getMulti($tagsKeys)->shouldBeCalledTimes(1)->willReturn($tagsValues);
        // End

        // Mock KeyValueStore::deleteMulti()
        $keysToDelete = [
            'key1',
            TaggablePoolInterface::KEY_TAGS_PREFIX.'key1',
            'key2',
            TaggablePoolInterface::KEY_TAGS_PREFIX.'key2',
            'key3',
            TaggablePoolInterface::KEY_TAGS_PREFIX.'key3',
        ];

        $this->storeMock->deleteMulti($keysToDelete)->shouldBeCalledTimes(1)->willReturn([true, true, true, true, true, true]);
        // End

        // When
        $actual = $this->buildPool()->invalidateTags($tags);

        // Then
        $this->assertTrue($actual);
    }

    /**
     * @return TaggablePool
     */
    private function buildPool(): TaggablePool
    {
        return new TaggablePool($this->storeMock->reveal());
    }
}
