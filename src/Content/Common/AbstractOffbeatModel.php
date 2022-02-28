<?php

namespace OffbeatWP\Content\Common;

use InvalidArgumentException;

abstract class AbstractOffbeatModel
{
    protected ?array $metaData = null;
    protected array $metaInput = [];
    protected array $metaToUnset = [];

    abstract public function getId(): ?int;
    abstract public function getMetaData(): array;

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

        $metas = $this->getMetaData();
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
    public function getMeta(string $key)
    {
        return $this->getRawMetaValue($key, null);
    }

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
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }

    /**
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
}