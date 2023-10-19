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

    /** @return mixed|false */
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
     * @return void
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
     * @param string $id
     * @param mixed $data
     * @param int|bool $lifeTime
     * @return void
     */
    public function save(string $id, $data, $lifeTime = 0): void
    {
        $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
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
