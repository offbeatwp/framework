<?php

namespace OffbeatWP\Content\Common;

use WP_Comment;
use WP_Post;
use WP_Term;
use WP_User;

abstract class OffbeatModel
{
    /** @var array<string, array<mixed>> */
    private array $metas = [];
    private bool $hasAllMetas = false;

    abstract public function getId(): int;
    abstract public function getWpObject(): WP_Post|WP_Term|WP_User|WP_Comment;
    abstract protected function getObjectType(): string;

    /** @return array<mixed> */
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
     * @return ($single is true ? mixed : array<mixed>)
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

    /** @return array<string, array<mixed>> */
    final public function getMetas(): array
    {
        if (!$this->hasAllMetas) {
            /** @var array<string, array<mixed>> $rawMeta */
            $rawMeta = get_metadata_raw($this->getObjectType(), $this->getId());
            $this->metas = $rawMeta;
            $this->hasAllMetas = true;
        }

        return $this->metas;
    }
}
