<?php

namespace OffbeatWP\Content\Common;

use Illuminate\Support\Collection;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

abstract class AbstractOffbeatModel
{
    protected ?array $metaData = null;
    protected array $metaToSet = [];
    protected array $metaToUnset = [];

    abstract public function getId(): ?int;
    abstract public function getMetaData(): array;

    /** @return scalar[]|null[]|array[] */
    public function getMetaValues(): array
    {
        $values = [];

        foreach ($this->getMetaData() as $key => $value) {
            $values[$key] = reset($value);
        }

        return $values;
    }

    public function setMeta(string $key, string|int|float|bool|array|null $value): static
    {
        $this->metaToSet[$key] = $value;
        unset($this->metaToUnset[$key]);

        return $this;
    }

    /** @param non-empty-string $key Metadata name. */
    public function unsetMeta(string $key): static
    {
        $this->metaToUnset[$key] = ''; // An empty string acts as a value wildcard when unsetting meta.
        unset($this->metaToSet[$key]);

        return $this;
    }

    abstract public function save(): ?int;

    /** @return positive-int */
    public function saveOrFail(): int
    {
        $result = $this->save();

        if ($result <= 0) {
            throw new OffbeatInvalidModelException('Failed to save ' . class_basename($this::class));
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

        if (array_key_exists($key, $this->metaToSet)) {
            return $this->metaToSet[$key];
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

        if (array_key_exists($key, $this->metaToSet)) {
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

    /////////////////////////////
    /// Static helper methods ///
    /////////////////////////////
    public static function find(?int $id): ?static
    {
        return ($id > 0) ? static::query()->findById($id) : null;
    }

    public static function findOrNew(int $id): ?static
    {
        return static::find($id) ?: static::create();
    }

    public static function findOrFail(int $id): static
    {
        $item = static::find($id);
        if (!$item) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    public static function create(): static
    {
        return new static();
    }
}