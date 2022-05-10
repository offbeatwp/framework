<?php

namespace OffbeatWP\Content\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use OffbeatWP\Content\Post\PostModel;

trait GetMetaTrait
{
    /**
     * @internal
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    private function getRawMetaValue(string $key, $defaultValue)
    {
        if (array_key_exists($key, $this->metaToUnset)) {
            return $defaultValue;
        }

        if (array_key_exists($key, $this->metaInput)) {
            return $this->metaInput[$key];
        }

        $metas = $this->getMetas();
        if ($metas && array_key_exists($key, $metas) && is_array($metas[$key])) {
            return reset($metas[$key]);
        }

        return $defaultValue;
    }

    /**
     * Returns the metaInput value if one with the given key exists.<br/>
     * If not, returns the meta value with the given key from the database.<br/>
     * If the value isn't in metaInput or the database, <i>null</i> is returned.
     * @param non-empty-string $key
     * @return mixed
     */
    public function getMetaValue(string $key)
    {
        return $this->getRawMetaValue($key, null);
    }

    /**
     * Check if a meta value exists at all.
     * @return bool True if the meta key exists, regardless of it's value. False if the meta key does not exist.
     */
    public function hasMeta(string $key): bool
    {
        if (array_key_exists($key, $this->metaToUnset)) {
            return false;
        }

        if (array_key_exists($key, $this->metaInput)) {
            return true;
        }

        $metas = $this->getMetas();
        return ($metas && array_key_exists($key, $metas));
    }

    /**
     * Retrieve a meta value as a string.<br/>
     * If the meta value does not exist or is falsy, then an <b>empty string</b> is returned.
     */
    public function getMetaString(string $key): string
    {
        return (string)$this->getRawMetaValue($key, '');
    }

    /**
     * Retrieve a meta value as an integer.<br/>
     * If the meta value does not exist or is falsy, then <b>0</b> is returned.
     */
    public function getMetaInt(string $key): int
    {
        return (int)$this->getRawMetaValue($key, 0);
    }

    /**
     * Retrieve a meta value as a floating point number.<br/>
     * If the meta value is falsy or does not exist, then <b>0</b> is returned.
     */
    public function getMetaFloat(string $key): float
    {
        return (float)$this->getRawMetaValue($key, 0);
    }

    /**
     * Retrieve a meta value as a boolean.<br/>
     * If the meta value does not exist then <b>false</b> is returned.
     */
    public function getMetaBool(string $key): bool
    {
        return (bool)$this->getRawMetaValue($key, false);
    }

    /**
     * Retrieve a meta value as an array.<br/>
     * If the meta value is falsy or does not exist, then <b>an empty array</b> is returned.
     */
    public function getMetaArray(string $key): array
    {
        $value = $this->getRawMetaValue($key, []);
        $value = is_serialized($value) ? unserialize($value, ['allowed_classes' => false]) : $value;
        return (array)$value;
    }

    /** @return PostModel[] */
    public function getMetaPostModels(string $key): array
    {
        $models = [];

        foreach ($this->getMetaArray($key) as $id) {
            if ($id && $model = offbeat('post')->get($id)) {
                $models[] = $model;
            }
        }

        return $models;
    }

    public function getMetaPostModel(string $key): ?PostModel
    {
        return offbeat('post')->get($this->getMetaInt($key));
    }

    /**
     * Retrieve a meta value as a collection.<br/>
     * If the meta value is falsy or does not exist, then <b>an empty collection</b> is returned.
     */
    public function getMetaCollection(string $key): Collection
    {
        return collect($this->getMetaArray($key));
    }

    /**
     * Retrieve a meta value as a Carbon Date.<br/>
     * If the meta value is falsy, does not exist, or cannot be parsed by carbon then <b>null</b> is returned.
     */
    public function getMetaCarbon(string $key): ?Carbon
    {
        $value = $this->getRawMetaValue($key, null);
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Exception $e) {
            return null;
        }
    }
}