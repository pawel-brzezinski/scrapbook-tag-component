<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag\Psr6;

use MatthiasMullie\Scrapbook\Psr6\{InvalidArgumentException, Item, Pool};
use Psr\Cache\CacheItemInterface;

/**
 * Taggable PSR-6 cache pool.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggablePool extends Pool implements TaggablePoolInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItem($key): TaggableItemInterface
    {
        $item = parent::getItem($key);

        return new TaggableItem($item->getKey(), $this->repository);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $this->assertValidKey($key);
        $keyTagsKey = $this->generateKeyTagsKey($key);

        $this->store->deleteMulti([$key, $keyTagsKey]);

        unset($this->deferred[$key]);
        unset($this->deferred[$keyTagsKey]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $keysToDelete = $keys;

        foreach ($keys as $key) {
            $this->assertValidKey($key);

            $keyTagKey = $this->generateKeyTagsKey($key);
            $keysToDelete[] = $keyTagKey;

            unset($this->deferred[$key]);
            unset($this->deferred[$keyTagKey]);
        }

        $this->store->deleteMulti($keysToDelete);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function save(CacheItemInterface $item)
    {
        return $this->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$item instanceof TaggableItemInterface) {
            return parent::saveDeferred($item);
        }

        $key = $item->getKey();
        $tags = $item->getTags();

        // Get current item tags
        $keyTagsKey = $this->generateKeyTagsKey($item->getKey());
        $currentTags = $this->store->get($keyTagsKey);
        // End

        // Save current tags
        $currentTagsItem = new Item($keyTagsKey, $this->repository);
        $currentTagsItem->set($tags);
        parent::saveDeferred($currentTagsItem);
        // End

        // Find tags from which key should be removed
        $removeKeyFromTags = [];

        if (is_array($currentTags)) {
            foreach ($currentTags as $currentTag) {
                if (!in_array($currentTag, $tags)) {
                    $removeKeyFromTags[] = $currentTag;
                }

                unset($currentTag);
            }
        }
        //

        // Fetch tag cache items (also fetch tags where key should be removed)
        $tagKeys = [];

        foreach (array_merge($tags, $removeKeyFromTags) as $tag) {
            $tagKeys[] = $this->generateTagKey($tag);
        }

        $tagValues = $this->store->getMulti($tagKeys);
        // End

        // Generate tag cache items with removed key
        $tagItems = $this->generateTagCacheItemWithRemovedKey($key, $removeKeyFromTags, $tagValues);

        foreach ($tagItems as $tagItem) {
            parent::saveDeferred($tagItem);
            unset($tagItem);
        }
        // End

        // Generate tag cache items with added key
        $tagItems = $this->generateTagCacheItemWithAddedKey($key, $tags, $tagValues);

        foreach ($tagItems as $tagItem) {
            parent::saveDeferred($tagItem);
            unset($tagItem);
        }
        // End

        return parent::saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags): bool
    {
        $tagKeys = [];

        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);

            if (!in_array($tagKey, $tagKeys)) {
                $tagKeys[] = $tagKey;
            }
        }

        $tagValues = $this->store->getMulti($tagKeys);
        $keysToDelete = [];

        foreach ($tagValues as $keys) {
            if (!is_array($keys)) {
                continue;
            }

            foreach ($keys as $key) {
                if (in_array($key, $keysToDelete)) {
                    continue;
                }

                $keysToDelete[] = $key;
                $keysToDelete[] = $this->generateKeyTagsKey($key);
            }
        }

        $this->store->deleteMulti($keysToDelete);

        return true;
    }

    /**
     * Generate key tags key string.
     *
     * @param string $key
     *
     * @return string
     */
    private function generateKeyTagsKey(string $key): string
    {
        return self::KEY_TAGS_PREFIX.$key;
    }

    /**
     * Generate tag key string.
     *
     * @param string $tag
     *
     * @return string
     */
    private function generateTagKey(string $tag): string
    {
        return self::TAG_PREFIX.$tag;
    }

    /**
     * Generate tag cache item with removed key.
     *
     * @param string $key
     * @param array $tags
     * @param array $values
     *
     * @return \Generator
     */
    private function generateTagCacheItemWithRemovedKey(string $key, array $tags, array $values)
    {
        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);
            $value = is_array($values[$tagKey]) ? $values[$tagKey] : [];
            $value = array_filter($value, function ($item) use ($key) {
                return $key !== $item;
            });

            $item = new Item($tagKey, $this->repository);
            $item->set(array_values($value));

            yield $item;
        }
    }

    /**
     * Generate tag cache item with added key.
     *
     * @param string $key
     * @param array $tags
     * @param array $values
     *
     * @return \Generator
     */
    private function generateTagCacheItemWithAddedKey(string $key, array $tags, array $values)
    {
        foreach ($tags as $tag) {
            $tagKey = $this->generateTagKey($tag);
            $value = isset($values[$tagKey]) && is_array($values[$tagKey]) ? $values[$tagKey] : [];

            if (!in_array($key, $value)) {
                $value[] = $key;
            }

            $item = new Item($tagKey, $this->repository);
            $item->set(array_values($value));

            yield $item;
        }
    }
}
