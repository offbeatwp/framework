<?php

namespace OffbeatWP\Components;

final class ComponentArrayCache
{
    /** @phpstan-var array<string, array{mixed, int|bool}>> $data each element being a tuple of [$data, $expiration], where the expiration is int|bool */
    private array $data = [];
    private string $namespace = '';
    private int $hitsCount = 0;
    private int $missesCount = 0;
    private ?int $namespaceVersion = 0;

    /** @return mixed */
    protected function doFetch(string $id)
    {
        if (!$this->doContains($id)) {
            $this->missesCount += 1;

            return false;
        }

        $this->hitsCount += 1;

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
     * @param string $id
     * @param mixed $data
     * @param int|bool $lifeTime
     * @return bool
     */
    protected function doSave(string $id, $data, $lifeTime = 0): bool
    {
        $this->data[$id] = [$data, $lifeTime ? time() + $lifeTime : false];

        return true;
    }

    protected function doDelete(string $id): bool
    {
        unset($this->data[$id]);

        return true;
    }

    /** @return mixed */
    public function fetch(string $id)
    {
        return $this->doFetch($this->getNamespacedId($id));
    }

    /**
     * @param string $id
     * @param mixed $data
     * @param int|bool $lifeTime
     * @return bool
     */
    public function save(string $id, $data, $lifeTime = 0): bool
    {
        return $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
    }

    private function getNamespacedId(string $id): string
    {
        $namespaceVersion = $this->getNamespaceVersion();

        return sprintf('%s[%s][%s]', $this->namespace, $id, $namespaceVersion);
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
