<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Psr6;

use PB\Extension\Scrapbook\Tag\Psr6\AbstractTaggableRepository;
use PB\Extension\Scrapbook\Tag\Psr6\TaggableItem;
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

    /** @var ObjectProphecy|AbstractTaggableRepository */
    private $repoMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->repoMock = $this->prophesize(AbstractTaggableRepository::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->repoMock = null;
    }

    public function testShouldReturnCurrentCacheItemTagsWhenCurrentTagsAreNotSetAndCacheItemIsHit()
    {
        // Given
        $expected = ['tag1', 'tag2'];

        // Mock TaggableRepositoryInterface::getCurrentTags()
        $this->repoMock->getCurrentTags(Argument::type('string'))->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        $itemUnderTest = $this->buildItem();
        Reflection::setPropertyValue($itemUnderTest, 'isHit', true);

        // When
        $actual = $itemUnderTest->getCurrentTags();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnEmptyCurrentCacheItemTagsWhenCurrentTagsAreNotSetAndCacheItemIsNotHit()
    {
        // Given
        $expected = [];

        // Mock TaggableRepositoryInterface::getCurrentTags()
        $this->repoMock->getCurrentTags(Argument::any())->shouldNotBeCalled();
        // End

        $itemUnderTest = $this->buildItem();
        Reflection::setPropertyValue($itemUnderTest, 'isHit', false);

        // When
        $actual = $itemUnderTest->getCurrentTags();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCurrentCacheItemTagsWhenCurrentTagsAreSet()
    {
        // Given
        $expected = ['tag1', 'tag2'];

        $itemUnderTest = $this->buildItem();
        $hash = Reflection::getPropertyValue($itemUnderTest, 'hash');
        Reflection::setPropertyValue($itemUnderTest, 'currentTags', [$hash => $expected]);

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
        // End

        // Mock TaggableRepositoryInterface::remove
        $this->repoMock->remove(Argument::type('string'))->shouldBeCalledTimes(1);
        // End

        return new TaggableItem(self::DEFAULT_KEY, $this->repoMock->reveal());
    }
}
