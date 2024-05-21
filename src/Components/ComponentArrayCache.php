<?php

namespace OffbeatWP\Components;

final class ComponentArrayCache
{
    /** @phpstan-var array<string|null, array{mixed, int<0, max>}>> $data each element being a tuple of [$data, $expiration], where the expiration is int */
    private array $data = [];

    public function has(string $id): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }

        $expiration = $this->data[$id][1];

        if ($expiration && $expiration < time()) {
            $this->delete($id);
            return false;
        }

        return true;
    }

    /**
     * Fetches an entry from the cache.
     * @param string $id The id of the cache entry to fetch.
     * @return string|null The cached data or <i>NULL</i>, if no cache entry exists for the given id.
     */
    public function fetch(string $id): ?string
    {
        if (!$this->has($id)) {
            return null;
        }

        return $this->data[$id][0];
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string $data The cache entry/data.
     * @param int<0, max> $lifeTime The lifetime. If != 0, sets a specific lifetime for this cache entry. (0 => infinite lifeTime)
     */
    public function save(string $id, string $data, int $lifeTime = 0): void
    {
        $this->data[$id] = [$data, $lifeTime ? time() + $lifeTime : 0];
    }

    /**
     * Deletes a cache entry.
     * @param string $id The cache id.
     * @return void
     */
    public function delete(string $id): void
    {
        unset($this->data[$id]);
    }
}