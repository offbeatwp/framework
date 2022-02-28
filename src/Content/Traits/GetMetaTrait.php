<?php

namespace OffbeatWP\Content\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

trait GetMetaTrait
{
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

        $metas = $this->getMetaData();
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
        return (array)$this->getRawMetaValue($key, []);
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