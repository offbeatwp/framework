<?php

namespace OffbeatWP\Components;

final class ComponentArrayCache
{
    /** @phpstan-var array<string, array{mixed, int|false}>> $data each element being a tuple of [$data, $expiration], where the expiration is int|bool */
    private array $data = [];
    private string $namespace = '';
    private ?int $namespaceVersion = 0;

    /** @return mixed */
    protected function doFetch(string $id)
    {
        if (!$this->doContains($id)) {
            return false;
        }

        return $this->data[$id][0];
    }

    protected function doContains(string $id): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }

        $expiration = $this->data[$id][1];

        if ($expiration && $expiration < time()) {
            $this->doDelete($id);

            return false;
        }

        return true;
    }

    /**
     * @param string $data
     * @param int|false $lifeTime
     */
    protected function doSave(string $id, $data, $lifeTime = 0): void
    {
        $this->data[$id] = [$data, $lifeTime ? time() + $lifeTime : false];
    }

    protected function doDelete(string $id): void
    {
        unset($this->data[$id]);
    }

    /** @return mixed */
    public function fetch(string $id)
    {
        return $this->doFetch($this->getNamespacedId($id));
    }

    /**
     * @param string $data
     * @param int|false $lifeTime
     */
    public function save(string $id, $data, $lifeTime = 0): void
    {
        $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
    }

    public function delete(string $id): void
    {
        $this->doDelete($this->getNamespacedId($id));
    }

    private function getNamespacedId(string $id): string
    {
        return sprintf('%s[%s][%d]', $this->namespace, $id, $this->getNamespaceVersion());
    }

    private function getNamespaceVersion(): int
    {
        if ($this->namespaceVersion !== null) {
            return $this->namespaceVersion;
        }

        $namespaceCacheKey = sprintf('OffbeatNamespaceCacheKey[%s]', $this->namespace);
        $this->namespaceVersion = (int)$this->doFetch($namespaceCacheKey) ?: 1;

        return $this->namespaceVersion;
    }
}
