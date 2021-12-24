<?php

namespace OffbeatWP\Content\Traits;

use Carbon\Carbon;
use Exception;

trait GetMetaTrait
{
    abstract public function getMeta(string $key, bool $single = true);

    /**
     * Retrieve a meta value as a string.<br/>
     * If the meta value is falsy, does not exist, or is not a scalar value then an <b>empty string</b> is returned.
     */
    public function getMetaString(string $key): string
    {
        $value = $this->getMeta($key);
        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * Retrieve a meta value as an integer.<br/>
     * If the meta value is falsy, does not exist, or is not a scalar value then <b>0</b> is returned.
     */
    public function getMetaInt(string $key): int
    {
        $value = $this->getMeta($key);
        return is_scalar($value) ? (int)$value : 0;
    }

    /**
     * Retrieve a meta value as a floating point number.<br/>
     * If the meta value is falsy, does not exist, or is not a scalar value then <b>0</b> is returned.
     */
    public function getMetaFloat(string $key): float
    {
        $value = $this->getMeta($key);
        return is_scalar($value) ? (float)$value : 0;
    }

    /**
     * Retrieve a meta value as a boolean.<br/>
     * If the meta value does not exist then <b>false</b> is returned.
     */
    public function getMetaBool(string $key): bool
    {
        return (bool)$this->getMeta($key);
    }

    /**
     * Retrieve a meta value as a Carbon Date.<br/>
     * If the meta value is falsy, does not exist, or cannot be parsed by carbon then <b>null</b> is returned.
     */
    public function getMetaCarbon(string $key): ?Carbon
    {
        $value = $this->getMeta($key);
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse(null);
        } catch (Exception $e) {
            return null;
        }
    }
}