<?php

declare(strict_types=1);

namespace PB\Tests\Extension\Scrapbook\Tag\Model;

use PB\Extension\Scrapbook\Tag\Model\TaggableCacheValue;
use PB\Tests\Extension\Scrapbook\Tag\Library\Reflection;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class TaggableCacheValueTest extends TestCase
{
    const DEFAULT_VALUE = 'cache-value';
    const DEFAULT_TAGS = ['foo', 'bar'];

    /**
     * @var TaggableCacheValue
     */
    private $modelUnderTest;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->modelUnderTest = new TaggableCacheValue(self::DEFAULT_VALUE, self::DEFAULT_TAGS);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->modelUnderTest = null;
    }

    public function constructDataProvider(): array
    {
        return [
            'default tags argument' => [self::DEFAULT_VALUE, [], [self::DEFAULT_VALUE]],
            'custom tags argument' => [self::DEFAULT_VALUE, self::DEFAULT_TAGS, [self::DEFAULT_VALUE, self::DEFAULT_TAGS]],
        ];
    }

    /**
     * @dataProvider constructDataProvider
     *
     * @param $expectedValue
     * @param array $expectedTags
     * @param array $args
     *
     * @throws \ReflectionException
     */
    public function testConstruct($expectedValue, array $expectedTags, array $args)
    {
        // Given
        $modelUnderTest = new TaggableCacheValue(...$args);

        // When
        $actualValue = Reflection::getPropertyValue($modelUnderTest, 'value');
        $actualTags = Reflection::getPropertyValue($modelUnderTest, 'tags');

        // Then
        $this->assertSame($expectedValue, $actualValue);
        $this->assertSame($expectedTags, $actualTags);
    }

    public function testGetValue()
    {
        // When
        $actual = $this->modelUnderTest->getValue();

        // Then
        $this->assertSame(self::DEFAULT_VALUE, $actual);
    }

    public function testGetTags()
    {
        // When
        $actual = $this->modelUnderTest->getTags();

        // Then
        $this->assertSame(self::DEFAULT_TAGS, $actual);
    }
}
