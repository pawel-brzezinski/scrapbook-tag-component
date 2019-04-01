<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Psr6;

use PB\Extension\Scrapbook\Tag\Model\TaggableCacheValue;
use PB\Extension\Scrapbook\Tag\Psr6\TaggableRepository;
use PB\Extension\Scrapbook\Tag\TaggableStoreInterface;
use PB\Tests\Extension\Scrapbook\Tag\Library\Reflection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggableRepositoryTest extends TestCase
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

    public function testAdd()
    {
        // Given
        $unique = 'unique';
        $key = 'foo';

        $repositoryUnderTest = $this->buildRepository();

        // When
        $repositoryUnderTest->add($unique, $key);
        $actualUnresolved = Reflection::getPropertyValue($repositoryUnderTest, 'unresolved');
        $actualUnresolvedCurrentTags = Reflection::getPropertyValue($repositoryUnderTest, 'unresolvedCurrentTags');

        // Then
        $this->assertArrayHasKey($unique, $actualUnresolved);
        $this->assertSame($key, $actualUnresolved[$unique]);

        $this->assertArrayHasKey($unique, $actualUnresolvedCurrentTags);
        $this->assertSame($key, $actualUnresolvedCurrentTags[$unique]);
    }

    public function testRemove()
    {
        // Given
        $unique = 'unique';
        $key = 'foo';

        $repositoryUnderTest = $this->buildRepository();
        $repositoryUnderTest->add($unique, $key);

        Reflection::setPropertyValue($repositoryUnderTest, 'resolved', ['unique-resolved' => 'key-resolved']);
        Reflection::setPropertyValue($repositoryUnderTest, 'resolvedCurrentTags', ['unique-resolved' => 'key-resolved']);

        // When
        $repositoryUnderTest->remove($unique);
        $repositoryUnderTest->remove('unique-resolved');

        $actualResolved = Reflection::getPropertyValue($repositoryUnderTest, 'resolved');
        $actualUnresolved = Reflection::getPropertyValue($repositoryUnderTest, 'unresolved');

        $actualResolvedCurrentTags = Reflection::getPropertyValue($repositoryUnderTest, 'resolvedCurrentTags');
        $actualUnresolvedCurrentTags = Reflection::getPropertyValue($repositoryUnderTest, 'unresolvedCurrentTags');

        // Then
        $this->assertArrayNotHasKey('unique-resolved', $actualResolved);
        $this->assertArrayNotHasKey('unique', $actualUnresolved);

        $this->assertArrayNotHasKey('unique-resolved', $actualResolvedCurrentTags);
        $this->assertArrayNotHasKey('unique', $actualUnresolvedCurrentTags);
    }

    public function testResolveTaggableCacheItems()
    {
        // Given
        $repositoryUnderTest = $this->buildRepository();
        $repositoryUnderTest->add('unique-1', 'key1');
        $repositoryUnderTest->add('unique-2', 'key1');
        $repositoryUnderTest->add('unique-3', 'key3');
        $repositoryUnderTest->add('unique-4', 'key4');
        $repositoryUnderTest->add('unique-5', 'key5');

        $expectedKeys = [0 => 'key1', 2 => 'key3', 3 => 'key4', 4 => 'key5'];
        $expectedResolvedTags = [
            'unique-1' => ['tag1', 'tag2'],
            'unique-2' => ['tag1', 'tag2'],
            'unique-3' => [],
            'unique-4' => [],
            'unique-5' => ['tag2'],
        ];

        // Mock TaggableStoreInterface::getMultiPlain()
        $values = [
            'key1' => new TaggableCacheValue('value1', ['tag1', 'tag2']),
            'key4' => 'not-taggable-cache-value',
            'key5' => new TaggableCacheValue('value5', ['tag2']),
        ];

        $this->storeMock->getMultiPlain($expectedKeys)->shouldBeCalledTimes(1)->willReturn($values);
        // End

        // When
        Reflection::callMethod($repositoryUnderTest, 'resolveTaggableCacheItems', []);
        $actualResolvedTags = Reflection::getPropertyValue($repositoryUnderTest, 'resolvedCurrentTags');
        $actualUnresolvedTags = Reflection::getPropertyValue($repositoryUnderTest, 'unresolvedCurrentTags');

        // Then
        $this->assertSame($expectedResolvedTags, $actualResolvedTags);
        $this->assertSame([], $actualUnresolvedTags);
    }

    public function testShouldReturnCacheItemCurrentTagsWhenCacheItemExistAndCurrentTagsAreNotResolvedButCanBeResolved()
    {
        // Given
        $unique = 'unique';
        $key = 'key';
        $resolved = [
            'unique' => 'cache value',
        ];

        $repositoryUnderTest = $this->buildRepository();
        Reflection::setPropertyValue($repositoryUnderTest, 'resolved', $resolved);
        Reflection::setPropertyValue($repositoryUnderTest, 'unresolvedCurrentTags', [$unique => $key]);

        $expected = ['tag1', 'tag2'];

        // Mock TaggableStoreInterface::getMultiPlain()
        $values = ['key' => new TaggableCacheValue('cache value', ['tag1', 'tag2'])];

        $this->storeMock->getMultiPlain(['key'])->shouldBeCalledTimes(1)->willReturn($values);
        // End

        // When
        $actual = $repositoryUnderTest->getCurrentTags($unique);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCacheItemCurrentTagsWhenCacheItemExistAndCurrentTagsAreNotResolvedAndCanNotBeResolved()
    {
        // Given
        $unique = 'unique';
        $key = 'key';
        $resolved = [
            'unique' => 'cache value',
        ];

        $repositoryUnderTest = $this->buildRepository();
        Reflection::setPropertyValue($repositoryUnderTest, 'resolved', $resolved);
        Reflection::setPropertyValue($repositoryUnderTest, 'unresolvedCurrentTags', [$unique => $key]);

        $expected = [];

        // Mock TaggableStoreInterface::getMultiPlain()
        $values = [];

        $this->storeMock->getMultiPlain(['key'])->shouldBeCalledTimes(1)->willReturn($values);
        // End

        // When
        $actual = $repositoryUnderTest->getCurrentTags($unique);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCacheItemCurrentTagsWhenCacheItemExistAndCurrentTagsAreResolved()
    {
        // Given
        $unique = 'unique';
        $key = 'key';
        $resolved = [
            'unique' => 'cache value',
        ];
        $resolvedCurrentTags = [
            'unique' => ['tag1', 'tag2'],
        ];

        $repositoryUnderTest = $this->buildRepository();
        Reflection::setPropertyValue($repositoryUnderTest, 'resolved', $resolved);
        Reflection::setPropertyValue($repositoryUnderTest, 'resolvedCurrentTags', $resolvedCurrentTags);

        $expected = ['tag1', 'tag2'];

        // Mock TaggableStoreInterface::getMultiPlain()
        $this->storeMock->getMultiPlain(Argument::any())->shouldNotBeCalled();
        // End

        // When
        $actual = $repositoryUnderTest->getCurrentTags($unique);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCacheItemCurrentTagsWhenCacheItemDoesNotExist()
    {
        // Given
        $unique = 'unique';
        $key = 'key';

        $repositoryUnderTest = $this->buildRepository();
        Reflection::setPropertyValue($repositoryUnderTest, 'unresolvedCurrentTags', [$unique => $key]);

        $expected = [];

        // Mock TaggableStoreInterface::getMultiPlain()
        $this->storeMock->getMultiPlain(Argument::any())->shouldNotBeCalled();
        // End

        // When
        $actual = $repositoryUnderTest->getCurrentTags($unique);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @return TaggableRepository
     */
    private function buildRepository(): TaggableRepository
    {
        return new TaggableRepository($this->storeMock->reveal());
    }
}
