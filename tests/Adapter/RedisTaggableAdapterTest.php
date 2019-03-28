<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Model;

use PB\Extension\Scrapbook\Tag\Adapter\{RedisTaggableAdapter, TaggableAdapterTrait};
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class RedisTaggableAdapterTest extends TestCase
{
    use TaggableAdapterTrait;

    /** @var ObjectProphecy|\Redis */
    private $redis;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->redis = $this->prophesize(\Redis::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->redis = null;
    }

    public function testAddKeyToTags()
    {
        // Given
        $key = 'foo';
        $tags = ['lorem', 'ipsum'];

        // Mock \Redis::sAdd()
        $this->redis->getOption(Argument::any())->shouldBeCalled();
        $this->redis->setOption(Argument::any(), Argument::any())->shouldBeCalled();
        $this->redis->sAdd($this->generateTagKey('lorem'), $key)->shouldBeCalledTimes(1)->willReturn(1);
        $this->redis->sAdd($this->generateTagKey('ipsum'), $key)->shouldBeCalledTimes(1)->willReturn(1);
        // End

        // When
        $actual = $this->buildAdapter()->addKeyToTags($key, $tags);

        // Then
        $this->assertSame(2, $actual);
    }

    public function testAddKeysToTags()
    {
        // Given
        $tags = [
            'tag_1' => ['key_1', 'key_2'],
            'tag_2' => ['key_1'],
        ];

        // Mock \Redis::sAdd()
        $this->redis->getOption(Argument::any())->shouldBeCalled();
        $this->redis->setOption(Argument::any(), Argument::any())->shouldBeCalled();
        $this->redis->sAdd($this->generateTagKey('tag_1'), 'key_1', 'key_2')->shouldBeCalledTimes(1)->willReturn(2);
        $this->redis->sAdd($this->generateTagKey('tag_2'), 'key_1')->shouldBeCalledTimes(1)->willReturn(1);
        // End

        // When
        $actual = $this->buildAdapter()->addKeysToTags($tags);

        // Then
        $this->assertSame(3, $actual);
    }

    public function testRemoveKeyFromTags()
    {
        // Given
        $key = 'foo';
        $tags = ['lorem', 'ipsum'];

        // Mock \Redis::sAdd()
        $this->redis->getOption(Argument::any())->shouldBeCalled();
        $this->redis->setOption(Argument::any(), Argument::any())->shouldBeCalled();
        $this->redis->srem($this->generateTagKey('lorem'), $key)->shouldBeCalledTimes(1)->willReturn(1);
        $this->redis->srem($this->generateTagKey('ipsum'), $key)->shouldBeCalledTimes(1)->willReturn(1);
        // End

        // When
        $actual = $this->buildAdapter()->removeKeyFromTags($key, $tags);

        // Then
        $this->assertSame(2, $actual);
    }

    public function testRemoveKeysFromTags()
    {
        // Given
        $tags = [
            'tag_1' => ['key_1', 'key_2'],
            'tag_2' => ['key_2'],
        ];

        // Mock \Redis::sAdd()
        $this->redis->getOption(Argument::any())->shouldBeCalled();
        $this->redis->setOption(Argument::any(), Argument::any())->shouldBeCalled();
        $this->redis->srem($this->generateTagKey('tag_1'), 'key_1', 'key_2')->shouldBeCalledTimes(1)->willReturn(2);
        $this->redis->srem($this->generateTagKey('tag_2'), 'key_2')->shouldBeCalledTimes(1)->willReturn(1);
        // End

        // When
        $actual = $this->buildAdapter()->removeKeysFromTags($tags);

        // Then
        $this->assertSame(3, $actual);
    }

    public function testGetTagsCacheKeys()
    {
        // Given
        $expected = ['foo', 'bar', 'example'];
        $tags = ['lorem', 'ipsum'];

        // Mock \Redis::sAdd()
        $this->redis->getOption(Argument::any())->shouldBeCalled();
        $this->redis->setOption(Argument::any(), Argument::any())->shouldBeCalled();
        $this->redis->sMembers($this->generateTagKey('lorem'))->shouldBeCalledTimes(1)->willReturn(['foo', 'bar']);
        $this->redis->sMembers($this->generateTagKey('ipsum'))->shouldBeCalledTimes(1)->willReturn(['foo', 'example']);
        // End

        // When
        $actual = $this->buildAdapter()->getTagsCacheKeys($tags);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @return RedisTaggableAdapter
     */
    private function buildAdapter(): RedisTaggableAdapter
    {
        return new RedisTaggableAdapter($this->redis->reveal());
    }
}
