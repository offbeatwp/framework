<?php

namespace OffbeatWP\Content\Common;

use Serializable;
use UnexpectedValueException;

/** @internal */
abstract class OffbeatObjectBuilder
{
    /** @var array<string, string|int|float|bool|array|Serializable> */
    protected array $metaToSet = [];
    /** @var array<string, array<int, string|int|float|bool|array|Serializable>> */
    protected array $metaToAdd = [];
    /** @var array<string, string|int|float|bool|array|Serializable|null> */
    protected array $metaToDelete = [];

    /**
     * @param non-empty-string $key
     * @param string|int|float|bool|mixed[]|\Serializable $value
     * @return $this
     */
    final public function addMeta(string $key, string|int|float|bool|array|Serializable $value)
    {
        if (!$key) {
            throw new UnexpectedValueException('Meta key passed to addMeta cannot be empty.');
        }

        if (array_key_exists($key, $this->metaToDelete)) {
            unset($this->metaToDelete[$key]);
        }

        if (!array_key_exists($key, $this->metaToAdd)) {
            $this->metaToAdd[$key] = [];
        }

        $this->metaToAdd[$key][] = $value;

        return $this;
    }

    /**
     * @param non-empty-string $key
     * @param string|int|float|bool|mixed[]|\Serializable $value
     * @return $this
     */
    final public function setMeta(string $key, string|int|float|bool|array|Serializable $value)
    {
        if (!$key) {
            throw new UnexpectedValueException('Meta key passed to setMeta cannot be empty.');
        }

        if (array_key_exists($key, $this->metaToDelete)) {
            unset($this->metaToDelete[$key]);
        } elseif (array_key_exists($key, $this->metaToAdd)) {
            unset($this->metaToAdd[$key]);
        }

        $this->metaToSet[$key] = $value;

        return $this;
    }

    /**
     * @param non-empty-string $key
     * @param string|int|float|bool|mixed[]|\Serializable|null $value
     * @return $this
     */
    final public function deleteMeta(string $key, string|int|float|bool|array|Serializable|null $value = null)
    {
        if (!$key) {
            throw new UnexpectedValueException('Meta key passed to deleteMeta cannot be empty.');
        }

        if ($value === '' || $value === false) {
            throw new UnexpectedValueException('The previous value check of the deleteMeta function cannot be an empty string.');
        }

        if (array_key_exists($key, $this->metaToAdd)) {
            unset($this->metaToAdd[$key]);
        }

        if (array_key_exists($key, $this->metaToSet)) {
            unset($this->metaToSet[$key]);
        }

        $this->metaToDelete[$key] = $value;

        return $this;
    }

    final protected function saveMeta(int $id): void
    {
        $type = $this->getObjectType()->value;

        while ($this->metaToSet) {
            update_metadata($type, $id, key($this->metaToSet), array_shift($this->metaToSet));
        }

        while ($this->metaToAdd) {
            $key = key($this->metaToAdd);

            foreach (array_shift($this->metaToAdd) as $value) {
                add_metadata($type, $id, $key, $value);
            }
        }

        while ($this->metaToDelete) {
            delete_metadata($type, $id, key($this->metaToDelete), array_shift($this->metaToDelete));
        }
    }

    abstract protected function getObjectType(): WpObjectTypeEnum;
}