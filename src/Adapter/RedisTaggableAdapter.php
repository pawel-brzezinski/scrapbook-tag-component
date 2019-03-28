<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Adapter;

use MatthiasMullie\Scrapbook\Adapters\Redis;

/**
 * Redis taggable adapter.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class RedisTaggableAdapter extends Redis implements TaggableAdapterInterface
{
    use TaggableAdapterTrait;

    /**
     * {@inheritdoc}
     */
    public function addKeyToTags(string $key, array $tags): int
    {
        $result = 0;

        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);
            $result += $this->client->sAdd($tagKey, $key);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function addKeysToTags(array $tags): int
    {
        $result = 0;

        foreach ($tags as $tag => $keys) {
            $tagKey = $this->generateTagKey($tag);
            $args = array_merge([$tagKey], $keys);

            $result += $this->client->sAdd(...$args);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function removeKeyFromTags(string $key, array $tags): int
    {
        $result = 0;

        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);
            $result += $this->client->sRem($tagKey, $key);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function removeKeysFromTags(array $tags): int
    {
        $result = 0;

        foreach ($tags as $tag => $keys) {
            $tagKey = $this->generateTagKey($tag);
            $args = array_merge([$tagKey], $keys);

            $result += $this->client->sRem(...$args);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagsCacheKeys(array $tags): array
    {
        $keys = [];

        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);
            $keys = array_merge($keys, $this->client->sMembers($tagKey));
        }

        return array_values(array_unique($keys));
    }
}
