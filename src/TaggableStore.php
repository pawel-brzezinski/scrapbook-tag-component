<?php

declare(strict_types=1);

namespace PB\Extension\Scrapbook\Tag;

use PB\Extension\Scrapbook\Tag\Adapter\TaggableAdapterInterface;
use PB\Extension\Scrapbook\Tag\Model\TaggableCacheValue;

/**
 * Taggable key-value store.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class TaggableStore implements TaggableStoreInterface
{
    /**
     * @var TaggableAdapterInterface
     */
    private $itemsStore;

    /**
     * @var TaggableAdapterInterface
     */
    private $tagsStore;

    /**
     * TaggableStore constructor.
     *
     * @param TaggableAdapterInterface $itemsStore
     * @param TaggableAdapterInterface|null $tagsStore
     */
    public function __construct(TaggableAdapterInterface $itemsStore, TaggableAdapterInterface $tagsStore = null)
    {
        $this->itemsStore = $itemsStore;
        $this->tagsStore = $tagsStore ?? $itemsStore;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlain(string $key, &$token = null)
    {
        return $this->itemsStore->get($key, $token);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, &$token = null)
    {
        $result = $this->getPlain($key, $token);

        if ($result instanceof TaggableCacheValue) {
            $result = $result->getValue();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiPlain(array $keys, &$tokens = null)
    {
        return $this->itemsStore->getMulti($keys, $tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function getMulti(array $keys, array &$tokens = null)
    {
        $result = array_map(function($item) {
            if ($item instanceof TaggableCacheValue) {
                return $item->getValue();
            }

            return $item;
        }, $this->getMultiPlain($keys, $tokens));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setWithTags(string $key, $value, array $tags = [], int $expire = 0): bool
    {
        $current = $this->getPlain($key);
        $value = new TaggableCacheValue($value, $tags);

        if ($current instanceof TaggableCacheValue) {
            $tagsToRemove = array_values(array_diff($current->getTags(), $tags));
            $this->tagsStore->removeKeyFromTags($key, $tagsToRemove);
        }

        $this->tagsStore->addKeyToTags($key, $tags);

        return $this->itemsStore->set($key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->setWithTags($key, $value, [], $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiWithTags(array $items, array $tags = [], int $expire = 0): array
    {
        $currents = $this->getMultiPlain(array_keys($items));
        $addToTags = [];
        $removeFromTags = [];

        array_walk($items, function(&$value, $key) use ($tags, $currents, &$addToTags, &$removeFromTags) {
            $current = $currents[$key] ?? null;
            $itemTags = $tags[$key] ?? [];

            // Add key to tags array
            foreach ($itemTags as $tag) {
               $addToTags[$tag][] = $key;
            }
            // End

            // Mark keys to remove from tags
            if ($current instanceof TaggableCacheValue) {
                $tagsDiff = array_values(array_diff($current->getTags(), $itemTags));

                foreach ($tagsDiff as $tag) {
                    $removeFromTags[$tag][] = $key;
                }
            }
            // End

            // Create TaggableCacheValue object
            $value = new TaggableCacheValue($value, $itemTags);
            // End
        });

        // Remove marked keys from tags
        $this->tagsStore->removeKeysFromTags($removeFromTags);
        // End

        // Add keys to tags
        $this->tagsStore->addKeysToTags($addToTags);
        // End

        return $this->itemsStore->setMulti($items, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function setMulti(array $items, $expire = 0)
    {
        return $this->setMultiWithTags($items, [], $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $item = $this->getPlain($key);

        if ($item instanceof TaggableCacheValue) {
            $this->tagsStore->removeKeyFromTags($key, $item->getTags());
        }

        return $this->itemsStore->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMulti(array $keys)
    {
        $items = $this->getMultiPlain($keys);
        $removeFromTags = [];

        foreach ($items as $key => $item) {
            if ($item instanceof TaggableCacheValue) {
                foreach ($item->getTags() as $tag) {
                    $removeFromTags[$tag][] = $key;
                }
            }
        }

        $this->tagsStore->removeKeysFromTags($removeFromTags);

        return $this->itemsStore->deleteMulti($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $value, $expire = 0)
    {
        $value = new TaggableCacheValue($value, []);

        return $this->itemsStore->add($key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function replace($key, $value, $expire = 0)
    {
        $value = new TaggableCacheValue($value, []);

        return $this->itemsStore->replace($key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function cas($token, $key, $value, $expire = 0)
    {
        $value = new TaggableCacheValue($value, []);

        return $this->itemsStore->cas($token, $key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $offset = 1, $initial = 0, $expire = 0)
    {
        return $this->itemsStore->increment($key, $offset, $initial, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $offset = 1, $initial = 0, $expire = 0)
    {
        return $this->itemsStore->decrement($key, $offset, $initial, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function touch($key, $expire)
    {
        return $this->itemsStore->touch($key, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        return $this->itemsStore->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection($name)
    {
        return $this->itemsStore->getCollection($name);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags): array
    {
        $keys = $this->tagsStore->getTagsCacheKeys($tags);

        return $this->deleteMulti($keys);
    }
}
