<?php

namespace OffbeatWP\Content\Common;

abstract class OffbeatModel
{
    /** @var array<string, list<mixed>> */
    private array $metas = [];
    private bool $hasAllMetas = false;

    abstract public function getId(): int;
    abstract protected function getObjectType(): string;

    /** @return list<mixed> */
    protected function getRawMeta(string $key): array
    {
        if (!array_key_exists($key, $this->metas)) {
            $rawMeta = get_metadata_raw($this->getObjectType(), $this->getId(), $key);
            $this->metas[$key] = is_array($rawMeta) ? $rawMeta : [];
        }

        return $this->metas[$key];
    }

    final public function hasMeta(string $key): bool
    {
        return (bool)$this->getRawMeta($key);
    }

    /**
     * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
     * @param bool $single Optional. Whether to return a single value. This parameter has no effect if `$key` is not specified. Default false.
     * @return ($single is true ? mixed : list<mixed>)
     */
    final public function getMeta(string $key, bool $single = true): mixed
    {
        $meta = $this->getRawMeta($key);

        if ($single) {
            return $meta ? $meta[0] : null;
        }

        return $meta;
    }

    final public function refreshMetas(): void
    {
        $this->hasAllMetas = false;
        $this->getMetas();
    }

    /** @return array<string, list<mixed>> */
    final public function getMetas(): array
    {
        if (!$this->hasAllMetas) {
            $this->metas = get_metadata_raw($this->getObjectType(), $this->getId());
            $this->hasAllMetas = true;
        }

        return $this->metas;
    }
}