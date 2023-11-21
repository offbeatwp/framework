<?php

namespace OffbeatWP\Components;

final class ComponentArrayCache
{
    /** @phpstan-var array<string|null, array{mixed, int<0, max>}>> $data each element being a tuple of [$data, $expiration], where the expiration is int|bool */
    private array $data = [];
    private string $namespace = '';

    protected function doContains(string $namespacedId): bool
    {
        if (!isset($this->data[$namespacedId])) {
            return false;
        }

        $expiration = $this->data[$namespacedId][1];

        if ($expiration && $expiration < time()) {
            $this->doDelete($namespacedId);
            return false;
        }

        return true;
    }

    /** @param int<0, max> $lifeTime */
    protected function doSave(string $namespacedId, string $data, int $lifeTime = 0): void
    {
        $this->data[$namespacedId] = [$data, $lifeTime ? time() + $lifeTime : false];
    }

    protected function doDelete(string $namespacedId): void
    {
        unset($this->data[$namespacedId]);
    }

    /**
     * Fetches an entry from the cache.
     * @param string $id The id of the cache entry to fetch.
     * @return string|null The cached data or <i>NULL</i>, if no cache entry exists for the given id.
     */
    public function fetch(string $id): ?string
    {
        $namespacedId = $this->getNamespacedId($id);

        if (!$this->doContains($namespacedId)) {
            return null;
        }

        return $this->data[$namespacedId][0];
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
        $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
    }

    /**
     * Deletes a cache entry.
     * @param string $id The cache id.
     * @return void
     */
    public function delete(string $id): void
    {
        $this->doDelete($this->getNamespacedId($id));
    }

    private function getNamespacedId(string $id): string
    {
        return $this->namespace . '['. $id. ']';
    }
}
