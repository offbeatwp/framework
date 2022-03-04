<?php

namespace OffbeatWP\Content\Common;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

abstract class AbstractOffbeatModel
{
    protected ?array $metaData = null;
    protected array $metaInput = [];
    protected array $metaToUnset = [];

    abstract public function getId(): ?int;
    abstract public function getMetaData(): array;

    public function getMetaValues(): array
    {
        $values = [];

        foreach ($this->getMetaData() as $key => $value) {
            if ($key[0] !== '_') {
                $values[$key] = reset($value);
            }
        }

        return $values;
    }

    /** @return static */
    public function setMeta(string $key, $value)
    {
        $this->metaInput[$key] = $value;

        unset($this->metaToUnset[$key]);

        return $this;
    }

    /**
     * @param non-empty-string $key Metadata name.
     * @return static
     */
    public function unsetMeta(string $key)
    {
        $this->metaToUnset[$key] = ''; // An empty string acts as a value wildcard when unsetting meta.

        unset($this->metaInput[$key]);

        return $this;
    }

    /**
     * Unset a meta-key with a specific value.
     * @param non-empty-string $key Metadata name.
     * @param true|float|int|non-empty-string|array|object $value Rows will only be removed that match the value. Must be serializable if non-scalar and cannot be false, null or an empty string.
     * @return static
     */
    public function unsetMetaWithValue(string $key, $value)
    {
        if ($value === '' || $value === null || $value === false) {
            throw new InvalidArgumentException('Cannot check for empty string, false or null values with unsetMetaWithValue.');
        }

        $this->metaToUnset[$key] = $value;

        unset($this->metaInput[$key]);

        return $this;
    }

    abstract public function save(): ?int;

    /** @return positive-int */
    public function saveOrFail(): int
    {
        $result = $this->save();

        if ($result <= 0) {
            throw new OffbeatInvalidModelException('Failed to save ' . $this->getBaseClassName());
        }

        return $result;
    }

    //////////////////////////////////////
    /// Methods for handeling metadata ///
    //////////////////////////////////////
    /**
     * @internal
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function getRawMetaValue(string $key, $defaultValue)
    {
        if (array_key_exists($key, $this->metaToUnset)) {
            return $defaultValue;
        }

        if (array_key_exists($key, $this->metaInput)) {
            return $this->metaInput[$key];
        }

        $dbMetas = $this->getMetaData();
        if ($dbMetas && array_key_exists($key, $dbMetas) && is_array($dbMetas[$key])) {
            return reset($dbMetas[$key]);
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
    public function getMeta(string $key)
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

    /////////////////////////////
    /// Static helper methods ///
    /////////////////////////////
    /** @return static|null */
    public static function find(?int $id)
    {
        return ($id) ? static::query()->findById($id) : null;
    }

    /** @return static */
    public static function findOrNew(?int $id)
    {
        return static::find($id) ?: static::create();
    }

    /** @return static */
    public static function findOrFail(int $id)
    {
        $item = static::find($id);
        if (!$item) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /** @return static[] */
    public static function allAsArray()
    {
        return static::query()->all()->toArray();
    }

    /** @return static */
    public static function create()
    {
        return new static(null);
    }

    ////////////////
    /// Internal ///
    ////////////////
    private function getBaseClassName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', __CLASS__);
    }
}