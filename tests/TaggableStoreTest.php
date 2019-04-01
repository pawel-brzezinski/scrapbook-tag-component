<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag;

use MatthiasMullie\Scrapbook\KeyValueStore;
use PB\Extension\Scrapbook\Tag\Adapter\TaggableAdapterInterface;
use PB\Extension\Scrapbook\Tag\Model\TaggableCacheValue;
use PB\Extension\Scrapbook\Tag\TaggableStore;
use PB\Tests\Extension\Scrapbook\Tag\Library\Reflection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggableStoreTest extends TestCase
{
    /** @var ObjectProphecy|TaggableAdapterInterface */
    private $isMock;

    /** @var ObjectProphecy|TaggableAdapterInterface */
    private $tsMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->isMock = $this->prophesize(TaggableAdapterInterface::class);
        $this->tsMock = $this->prophesize(TaggableAdapterInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->isMock = null;
        $this->tsMock = null;
    }

    public function constructDataProvider(): array
    {
        $isMock = $this->prophesize(TaggableAdapterInterface::class)->reveal();
        $tsMock = $this->prophesize(TaggableAdapterInterface::class)->reveal();

        return [
            'default tags store instance' => [$isMock, $isMock, [$isMock]],
            'custom tags store instance' => [$isMock, $tsMock, [$isMock, $tsMock]],
        ];
    }

    /**
     * @dataProvider constructDataProvider
     *
     * @param TaggableAdapterInterface $expectedItemsStore
     * @param TaggableAdapterInterface $expectedTagsStore
     * @param array $args
     *
     * @throws \ReflectionException
     */
    public function testConstruct(
        TaggableAdapterInterface $expectedItemsStore,
        TaggableAdapterInterface $expectedTagsStore,
        array $args
    ) {
        // Given
        $storeUnderTest = new TaggableStore(...$args);

        // When
        $actualItemsStore = Reflection::getPropertyValue($storeUnderTest, 'itemsStore');
        $actualTagsStore = Reflection::getPropertyValue($storeUnderTest, 'tagsStore');

        // Then
        $this->assertSame($expectedItemsStore, $actualItemsStore);
        $this->assertSame($expectedTagsStore, $actualTagsStore);
    }

    public function getPlainDataProvider(): array
    {
        $cacheValue = new TaggableCacheValue('cache value', ['foo']);

        return [
            'default token' => [$cacheValue, ['foobar']],
            'custom token' => [$cacheValue, ['foobar', 'some-token']],
        ];
    }

    /**
     * @dataProvider getPlainDataProvider
     *
     * @param $expected
     * @param array $args
     */
    public function testGetPlain($expected, array $args)
    {
        // Given
        $expectedToken = $args[1] ?? null;

        // Mock TaggableAdapterInterface::get()
        $this->isMock->get($args[0], $expectedToken)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->getPlain(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function getDataProvider(): array
    {
        $cacheValue = new TaggableCacheValue('cache value', ['foo']);

        return [
            'cache value object as result and default token argument' => ['cache value', $cacheValue, ['foobar']],
            'not cache value object as result and custom token argument' => ['cache value', 'cache value', ['foobar', 'some-token']],
        ];
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param $expected
     * @param $expectedCacheValue
     * @param array $args
     */
    public function testGet($expected, $expectedCacheValue, array $args)
    {
        // Given
        $expectedToken = $args[1] ?? null;

        // Mock TaggableAdapterInterface::get()
        $this->isMock->get($args[0], $expectedToken)->shouldBeCalledTimes(1)->willReturn($expectedCacheValue);
        // End

        // When
        $actual = $this->buildStore()->get(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function getMultiPlainDataProvider(): array
    {
        $cacheValue1 = new TaggableCacheValue('cache value 1', ['foo']);
        $cacheValue2 = new TaggableCacheValue('cache value 2', ['foo']);

        return [
            'default tokens' => [['foo' => $cacheValue1, 'bar' => $cacheValue2], [['foo', 'bar']]],
            'custom tokens' => [['foo' => $cacheValue1, 'bar' => $cacheValue2], [['foo', 'bar'], ['foo' => 'token-1', 'bar' => 'token-2']]],
        ];
    }

    /**
     * @dataProvider getMultiPlainDataProvider
     *
     * @param array $expected
     * @param array $args
     */
    public function testGetMultiPlain(array $expected, array $args)
    {

        // Given
        $expectedTokens = $args[1] ?? null;

        // Mock TaggableAdapterInterface::getMulti()
        $this->isMock->getMulti($args[0], $expectedTokens)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->getMultiPlain(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function getMultiDataProvider(): array
    {
        $cacheValue1 = new TaggableCacheValue('cache value 1', ['foo']);
        $cacheValue2 = new TaggableCacheValue('cache value 2', ['foo']);

        return [
            'cache value objects as result and default tokens argument' => [
                ['foo' => 'cache value 1', 'bar' => 'cache value 2'],
                ['foo' => $cacheValue1, 'bar' => $cacheValue2],
                [['foo', 'bar']],
            ],
            'not cache value object as result and custom token argument' => [
                ['foo' => 'cache value 1', 'bar' => 'cache value 2'],
                ['foo' => 'cache value 1', 'bar' => 'cache value 2'],
                [['foo', 'bar'], ['foo' => 'token-1', 'bar' => 'token-2']],
            ],
        ];
    }

    /**
     * @dataProvider getMultiDataProvider
     *
     * @param array $expected
     * @param array $expectedCacheValue
     * @param array $args
     */
    public function testGetMulti(array $expected, array $expectedCacheValue, array $args)
    {
        // Given
        $expectedTokens = $args[1] ?? null;

        // Mock TaggableAdapterInterface::get()
        $this->isMock->getMulti($args[0], $expectedTokens)->shouldBeCalledTimes(1)->willReturn($expectedCacheValue);
        // End

        // When
        $actual = $this->buildStore()->getMulti(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function setWithTagsDataProvider(): array
    {
        // Dataset 1 - default tags and default expire
        $current1 = null;
        $tagsToRemove1 = [];
        $args1 = ['foo', 'bar'];
        // End

        // Dataset 2 - custom tags and custom expire
        $current2 = null;
        $tagsToRemove2 = [];
        $args2 = ['foo', 'bar', ['lorem', 'ipsum'], 100];
        // End

        // Dataset 3 - current cache value is taggable object and has the same tags
        $current3 = new TaggableCacheValue('old value', ['ipsum', 'lorem']);
        $tagsToRemove3 = [];
        $args3 = ['foo', 'bar', ['lorem', 'ipsum']];
        // End

        // Dataset 4 - current cache value is taggable object and has not the same tags
        $current4 = new TaggableCacheValue('old value', ['ipsum', 'lorem', 'example']);
        $tagsToRemove4 = ['example'];
        $args4 = ['foo', 'bar', ['lorem', 'ipsum']];
        // End

        return [
            'default tags and default expire' => [true, $current1, $tagsToRemove1, $args1],
            'custom tags and custom expire' => [true, $current2, $tagsToRemove2, $args2],
            'current cache value is taggable object and has the same tags' => [true, $current3, $tagsToRemove3, $args3],
            'current cache value is taggable object and has not the same tags' => [true, $current4, $tagsToRemove4, $args4],
        ];
    }

    /**
     * @dataProvider setWithTagsDataProvider
     *
     * @param bool $expected
     * @param $currentCacheValue
     * @param array $tagsToRemove
     * @param array $args
     */
    public function testSetWithTags(bool $expected, $currentCacheValue, array $tagsToRemove, array $args)
    {
        // Given
        $expectedTags = $args[2] ?? [];
        $expectedExpire = $args[3] ?? 0;
        $expectedValue = new TaggableCacheValue($args[1], $expectedTags);

        // Mock TaggableAdapterInterface::getPlain() - items store
        $this->isMock->get($args[0], null)->shouldBeCalledTimes(1)->willReturn($currentCacheValue);
        // End

        // Mock TaggableAdapterInterface::removeKeyFromTags()
        if ($currentCacheValue instanceof TaggableCacheValue) {
            $this->tsMock->removeKeyFromTags($args[0], $tagsToRemove)->shouldBeCalledTimes(1);
        }
        // End

        // Mock TaggableAdapterInterface::addKeyToTags()
        $this->tsMock->addKeyToTags($args[0], $expectedTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::set() - items store
        $this->isMock->set($args[0], $expectedValue, $expectedExpire)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->setWithTags(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function setProvider(): array
    {
        // Dataset 1 - default expire
        $current1 = null;
        $tagsToRemove1 = [];
        $args1 = ['foo', 'bar'];
        // End

        // Dataset 2 - custom expire
        $current2 = null;
        $tagsToRemove2 = [];
        $args2 = ['foo', 'bar', 100];
        // End

        return [
            'default expire' => [true, $current1, $tagsToRemove1, $args1],
            'custom expire' => [true, $current2, $tagsToRemove2, $args2],
        ];
    }

    /**
     * @dataProvider setProvider
     *
     * @param bool $expected
     * @param $currentCacheValue
     * @param array $tagsToRemove
     * @param array $args
     */
    public function testSet(bool $expected, $currentCacheValue, array $tagsToRemove, array $args)
    {
        // Given
        $expectedTags = [];
        $expectedExpire = $args[2] ?? 0;
        $expectedValue = new TaggableCacheValue($args[1], $expectedTags);

        // Mock TaggableAdapterInterface::getPlain() - items store
        $this->isMock->get($args[0], null)->shouldBeCalledTimes(1)->willReturn($currentCacheValue);
        // End

        // Mock TaggableAdapterInterface::removeKeyFromTags()
        if ($currentCacheValue instanceof TaggableCacheValue) {
            $this->tsMock->removeKeyFromTags($args[0], $tagsToRemove)->shouldBeCalledTimes(1);
        }
        // End

        // Mock TaggableAdapterInterface::addKeyToTags()
        $this->tsMock->addKeyToTags($args[0], $expectedTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::set() - items store
        $this->isMock->set($args[0], $expectedValue, $expectedExpire)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->set(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function setMultiWithTagsDataProvider(): array
    {
        // Dataset 1 - default tags and default expire
        $items1 = ['foo' => 'bar', 'lorem' => 'ipsum'];
        $args1 = [$items1];
        $currents1 = ['foo' => null, 'bar' => null];
        $removeFromTags1 = [];
        $expected1 = ['foo' => true, 'lorem' => true];
        // End

        // Dataset 2 - custom tags and custom expire
        $items2 = ['foo' => 'bar', 'lorem' => 'ipsum'];
        $args2 = [$items2, ['foo' => ['tag_1', 'tag_2'], 'lorem' => ['tag_2']], 100];
        $currents2 = ['foo' => null, 'bar' => null];
        $removeFromTags2 = [];
        $expected2 = ['foo' => true, 'lorem' => true];
        // End

        // Dataset 3 - current cache values exists and they have the same tags as new ones
        $items3 = ['foo' => 'bar', 'lorem' => 'ipsum'];
        $args3 = [$items3, ['foo' => ['tag_1', 'tag_2'], 'lorem' => ['tag_2']], 100];
        $currents3 = [
            'foo' => new TaggableCacheValue('old-value', ['tag_1', 'tag_2']),
            'lorem' => new TaggableCacheValue('old-value', ['tag_2']),
        ];
        $removeFromTags3 = [];
        $expected3 = ['foo' => true, 'lorem' => true];
        // End

        // Dataset 4 - current cache values exists and they have tags which does not exist in new ones
        $items4 = ['foo' => 'bar', 'lorem' => 'ipsum'];
        $args4 = [$items4, ['foo' => ['tag_1', 'tag_2'], 'lorem' => ['tag_2']], 100];
        $currents4 = [
            'foo' => new TaggableCacheValue('old-value', ['tag_1', 'tag_2', 'tag_3', 'tag_4']),
            'lorem' => new TaggableCacheValue('old-value', ['tag_2', 'tag_3']),
        ];
        $removeFromTags4 = [
            'tag_3' => ['foo', 'lorem'],
            'tag_4' => ['foo'],
        ];
        $expected4 = ['foo' => true, 'lorem' => true];
        // End

        return [
            'default tags and default expire' => [$expected1, $currents1, $removeFromTags1, $args1],
            'custom tags and custom expire' => [$expected2, $currents2, $removeFromTags2, $args2],
            'current cache values exists and they have the same tags as new one' => [
                $expected3, $currents3, $removeFromTags3, $args3,
            ],
            'current cache values exists and they have tags which does not exist in new ones' => [
                $expected4, $currents4, $removeFromTags4, $args4,
            ],
        ];
    }

    /**
     * @dataProvider setMultiWithTagsDataProvider
     *
     * @param array $expected
     * @param array $currents
     * @param array $removeFromTags
     * @param array $args
     */
    public function testSetMultiWithTags(array $expected, array $currents, array $removeFromTags, array $args)
    {
        // Given
        $expectedTags = $args[1] ?? [];
        $expectedExpire = $args[2] ?? 0;
        $expectedItems = $args[0];

        array_walk($expectedItems, function(&$value, $key) use ($expectedTags) {
            $tags = $expectedTags[$key] ?? [];
            $value = new TaggableCacheValue($value, $tags);
        });

        $expectedAddKeysToTags = [];

        foreach ($expectedTags as $key => $tags) {
            foreach ($tags as $tag) {
                $expectedAddKeysToTags[$tag][] = $key;
            }
        }

        // Mock TaggableAdapterInterface::getMulti() - items store
        $this->isMock->getMulti(array_keys($expectedItems), null)->shouldBeCalledTimes(1)->willReturn($currents);
        // End

        // Mock TaggableAdapterInterface::removeKeysFromTags() - tags store
        $this->tsMock->removeKeysFromTags($removeFromTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::addKeysToTags() - tags store
        $this->tsMock->addKeysToTags($expectedAddKeysToTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::setMulti() - items store
        $this->isMock->setMulti($expectedItems, $expectedExpire)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->setMultiWithTags(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function setMultiDataProvider(): array
    {
        // Dataset 1 - default tags and default expire
        $items1 = ['foo' => 'bar', 'lorem' => 'ipsum'];
        $args1 = [$items1];
        $currents1 = ['foo' => null, 'bar' => null];
        $removeFromTags1 = [];
        $expected1 = ['foo' => true, 'lorem' => true];
        // End

        // Dataset 2 - custom tags and custom expire
        $items2 = ['foo' => 'bar', 'lorem' => 'ipsum'];
        $args2 = [$items2, 100];
        $currents2 = ['foo' => null, 'bar' => null];
        $removeFromTags2 = [];
        $expected2 = ['foo' => true, 'lorem' => true];
        // End

        return [
            'default expire' => [$expected1, $currents1, $removeFromTags1, $args1],
            'custom expire' => [$expected2, $currents2, $removeFromTags2, $args2],
        ];
    }

    /**
     * @dataProvider setMultiDataProvider
     *
     * @param array $expected
     * @param array $currents
     * @param array $removeFromTags
     * @param array $args
     */
    public function testSetMulti(array $expected, array $currents, array $removeFromTags, array $args)
    {
        // Given
        $expectedTags = [];
        $expectedExpire = $args[1] ?? 0;
        $expectedItems = $args[0];

        array_walk($expectedItems, function(&$value, $key) use ($expectedTags) {
            $tags = $expectedTags[$key] ?? [];
            $value = new TaggableCacheValue($value, $tags);
        });

        // Mock TaggableAdapterInterface::getMulti() - items store
        $this->isMock->getMulti(array_keys($expectedItems), null)->shouldBeCalledTimes(1)->willReturn($currents);
        // End

        // Mock TaggableAdapterInterface::removeKeysFromTags() - tags store
        $this->tsMock->removeKeysFromTags($removeFromTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::addKeysToTags() - tags store
        $this->tsMock->addKeysToTags($expectedTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::setMulti() - items store
        $this->isMock->setMulti($expectedItems, $expectedExpire)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->setMulti(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function deleteDataProvider(): array
    {
        $cacheValue = new TaggableCacheValue('cache-value', ['tag1', 'tag2']);

        return [
            'cache value is taggable value' => [true, $cacheValue, 'foo'],
            'cache value is not taggable value' => [true, 'some-content', 'foo'],
        ];
    }

    /**
     * @dataProvider deleteDataProvider
     *
     * @param bool $expected
     * @param $cacheValue
     * @param string $key
     */
    public function testDelete(bool $expected, $cacheValue, string $key)
    {
        // Given

        // Mock TaggableAdapterInterface::getPlain() - items store
        $this->isMock->get($key, null)->shouldBeCalledTimes(1)->willReturn($cacheValue);
        // End

        // Mock TaggableAdapterInterface::removeKeyFromTags() - tags store
        if ($cacheValue instanceof TaggableCacheValue) {
            $this->tsMock->removeKeyFromTags($key, $cacheValue->getTags())->shouldBeCalledTimes(1);
        } else {
            $this->tsMock->removeKeyFromTags(Argument::any(), Argument::any())->shouldNotBeCalled();
        }
        // End

        // Mock TaggableAdapterInterface::delete() - items store
        $this->isMock->delete($key)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->delete($key);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testDeleteMulti()
    {
        // Given
        $keys = ['foo', 'bar'];
        $items = [
            'foo' => new TaggableCacheValue('cache-value', ['tag1', 'tag2']),
            'bar' => new TaggableCacheValue('cache-value', ['tag2']),
        ];
        $removeFromTags = ['tag1' => ['foo'], 'tag2' => ['foo', 'bar']];
        $expected = ['foo' => true, 'bar' => true];

        // Mock TaggableAdapterInterface::getMulti() - items store
        $this->isMock->getMulti($keys, null)->shouldBeCalledTimes(1)->willReturn($items);
        // End

        // Mock TaggableAdapterInterface::removeKeysFromTags() - tags store
        $this->tsMock->removeKeysFromTags($removeFromTags)->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::deleteMulti() - items store
        $this->isMock->deleteMulti($keys)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->deleteMulti($keys);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function addDataProvider(): array
    {
        return [
            'default expire' => [true, ['foo', 'bar']],
            'custom expire' => [true, ['foo', 'bar', 100]],
        ];
    }

    /**
     * @dataProvider addDataProvider
     *
     * @param bool $expected
     * @param array $args
     */
    public function testAdd(bool $expected, array $args)
    {
        // Given
        $expectedValue = new TaggableCacheValue($args[1], []);
        $expectedExpiry = $args[2] ?? 0;

        // Mock TaggableAdapterInterface::add() - items store
        $this->isMock->add($args[0], $expectedValue, $expectedExpiry)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->add(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function replaceDataProvider(): array
    {
        return [
            'default expire' => [true, ['foo', 'bar']],
            'custom expire' => [true, ['foo', 'bar', 100]],
        ];
    }

    /**
     * @dataProvider replaceDataProvider
     *
     * @param bool $expected
     * @param array $args
     */
    public function testReplace(bool $expected, array $args)
    {
        // Given
        $expectedValue = new TaggableCacheValue($args[1], []);
        $expectedExpiry = $args[2] ?? 0;

        // Mock TaggableAdapterInterface::replace() - items store
        $this->isMock->replace($args[0], $expectedValue, $expectedExpiry)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->replace(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function casDataProvider(): array
    {
        return [
            'default expire' => [true, ['token', 'foo', 'bar']],
            'custom expire' => [true, ['token', 'foo', 'bar', 100]],
        ];
    }

    /**
     * @dataProvider casDataProvider
     *
     * @param bool $expected
     * @param array $args
     */
    public function testCas(bool $expected, array $args)
    {
        // Given
        $expectedValue = new TaggableCacheValue($args[2], []);
        $expectedExpiry = $args[3] ?? 0;

        // Mock TaggableAdapterInterface::cas() - items store
        $this->isMock->cas($args[0], $args[1], $expectedValue, $expectedExpiry)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->cas(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function incrementDataProvider(): array
    {
        return [
            'default arguments' => [10, ['foo']],
            'custom arguments' => [11, ['foo', 5, 4, 100]],
        ];
    }

    /**
     * @dataProvider incrementDataProvider
     *
     * @param int $expected
     * @param array $args
     */
    public function testIncrement(int $expected, array $args)
    {
        // Given
        $expectedOffset = $args[1] ?? 1;
        $expectedInitial = $args[2] ?? 0;
        $expectedExpiry = $args[3] ?? 0;

        // Mock TaggableAdapterInterface::increment() - items store
        $this->isMock
            ->increment($args[0], $expectedOffset, $expectedInitial, $expectedExpiry)
            ->shouldBeCalledTimes(1)
            ->willReturn($expected)
        ;
        // End

        // When
        $actual = $this->buildStore()->increment(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function decrementDataProvider(): array
    {
        return [
            'default arguments' => [10, ['foo']],
            'custom arguments' => [11, ['foo', 5, 4, 100]],
        ];
    }

    /**
     * @dataProvider decrementDataProvider
     *
     * @param int $expected
     * @param array $args
     */
    public function testDecrement(int $expected, array $args)
    {
        // Given
        $expectedOffset = $args[1] ?? 1;
        $expectedInitial = $args[2] ?? 0;
        $expectedExpiry = $args[3] ?? 0;

        // Mock TaggableAdapterInterface::decrement() - items store
        $this->isMock
            ->decrement($args[0], $expectedOffset, $expectedInitial, $expectedExpiry)
            ->shouldBeCalledTimes(1)
            ->willReturn($expected)
        ;
        // End

        // When
        $actual = $this->buildStore()->decrement(...$args);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testTouch()
    {
        // Given
        $key = 'foo';
        $expire = 123;
        $expected = true;

        // Mock TaggableAdapterInterface::touch() - items store
        $this->isMock->touch($key, $expire)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->touch($key, $expire);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testFlush()
    {
        // Given
        $expected = true;

        // Mock TaggableAdapterInterface::flush() - items store
        $this->isMock->flush()->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->flush();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testGetCollection()
    {
        // Given
        $name = 'foo-collection';
        $expected = $this->prophesize(KeyValueStore::class)->reveal();

        // Mock TaggableAdapterInterface::getCollection() - items store
        $this->isMock->getCollection($name)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->getCollection($name);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testInvalidateTags()
    {
        // Given
        $tags = ['tag1', 'tag2'];
        $expectedKeys = ['key1', 'key2', 'key3', 'key4'];
        $expectedItems = [
            'key1' => 'Value 1',
            'key2' => 'Value 2',
            'key3' => 'Value 3',
            'key4' => 'Value 4',
        ];
        $expected = ['key1' => true, 'key2' => true, 'key3' => true, 'key4' => true];

        // Mock TaggableAdapterInterface::getTagsCacheKeys() - tags store
        $this->tsMock->getTagsCacheKeys($tags)->shouldBeCalledTimes(1)->willReturn($expectedKeys);
        // End

        // Mock TaggableAdapterInterface::getMulti() - items store
        $this->isMock->getMulti($expectedKeys, null)->shouldBeCalledTimes(1)->willReturn($expectedItems);
        // End

        // Mock TaggableAdapterInterface::removeKeysFromTags() - tags store
        $this->tsMock->removeKeysFromTags([])->shouldBeCalledTimes(1);
        // End

        // Mock TaggableAdapterInterface::deleteMulti() - items store
        $this->isMock->deleteMulti($expectedKeys)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->invalidateTags($tags);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testAddKeyToTags()
    {
        // Given
        $key = 'foo';
        $tags = ['tag1', 'tag2'];
        $expected = 2;

        // Mock TaggableAdapterInterface::addKeyToTags() - tags store
        $this->tsMock->addKeyToTags($key, $tags)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->addKeyToTags($key, $tags);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testAddKeysToTags()
    {
        // Given
        $tags = ['tag1' => ['foo', 'bar'], 'tag2' => ['bar']];
        $expected = 3;

        // Mock TaggableAdapterInterface::addKeysToTags() - tags store
        $this->tsMock->addKeysToTags($tags)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildStore()->addKeysToTags($tags);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @return TaggableStore
     */
    private function buildStore(): TaggableStore
    {
        return new TaggableStore($this->isMock->reveal(), $this->tsMock->reveal());
    }
}
