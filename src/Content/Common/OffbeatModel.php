<?php

namespace OffbeatWP\Content\Common;

abstract class OffbeatModel
{
    /** @var array<string, list<mixed>> */
    private array $metas = [];
    private bool $hasAllMetas = false;

    abstract public function getId(): int;
    abstract protected function getObjectType(): string;

    /**
     * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
     * @param bool $single Optional. Whether to return a single value. This parameter has no effect if `$key` is not specified. Default false.
     * @return ($single is true ? mixed : list<mixed>)
     */
    final public function getMeta(string $key, bool $single = true): mixed
    {
        if (!array_key_exists($key, $this->metas)) {
            $this->metas[$key] = get_metadata_raw($this->getObjectType(), $this->getId(), $key);
        }

        if ($single) {
            return $this->metas[$key][0] ?? null;
        }

        return $this->metas[$key];
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