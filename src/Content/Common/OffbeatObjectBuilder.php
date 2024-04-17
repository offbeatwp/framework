<?php

namespace OffbeatWP\Content\Common;

use Serializable;

/** @internal */
abstract class OffbeatObjectBuilder
{
    /** @var array<string, string|int|float|bool|array|Serializable> */
    protected array $metaToSet = [];
    /** @var array<string, string|int|float|bool|array|Serializable>[] */
    protected array $metaToAdd = [];
    /** @var array<string, mixed> */
    protected array $metaToDelete = [];

    final public function addMeta(string $key, string|int|float|bool|array|Serializable $value)
    {
        if (array_key_exists($key, $this->metaToDelete)) {
            unset($this->metaToDelete[$key]);
        }

        if (!array_key_exists($key, $this->metaToAdd)) {
            $this->metaToAdd[$key] = [];
        }

        $this->metaToAdd[$key][] = $value;
    }

    final public function setMeta(string $key, string|int|float|bool|array|Serializable $value)
    {
        if (array_key_exists($key, $this->metaToDelete)) {
            unset($this->metaToDelete[$key]);
        } elseif (array_key_exists($key, $this->metaToAdd)) {
            unset($this->metaToAdd[$key]);
        }

        $this->metaToSet[$key] = $value;
    }

    final public function deleteMeta(string $key)
    {
        if (array_key_exists($key, $this->metaToAdd)) {
            unset($this->metaToAdd[$key]);
        }

        if (array_key_exists($key, $this->metaToSet)) {
            unset($this->metaToSet[$key]);
        }

        $this->metaToDelete[$key] = '';
    }
}