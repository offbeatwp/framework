<?php

namespace OffbeatWP\Content\Traits;

trait OffbeatQueryTrait
{
    protected static string $lastRequest = '';

    /**
     * Get objects where the meta value is equal to the given value.
     * @param string $metaKey
     * @param scalar|null $value
     * @return $this
     */
    final public function whereMetaIs(string $metaKey, string|int|bool|float|null $value)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => '==', 'value' => $value]);
        return $this;
    }

    /**
     * Get objects where the meta value is NOT equal to the given value.
     * @param string $metaKey
     * @param scalar|null $value
     * @return $this
     */
    final public function whereMetaIsNot(string $metaKey, string|int|bool|float|null $value)
    {
        $this->whereMeta([
            'relation' => 'OR',
            ['key' => $metaKey, 'compare' => '!=', 'value' => $value],
            ['key' => $metaKey, 'compare' => 'NOT EXISTS']
        ]);

        return $this;
    }

    /**
     * @param string $metaKey
     * @param mixed[] $values
     * @return $this
     */
    final public function whereMetaIn(string $metaKey, array $values)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'IN', 'value' => $values]);
        return $this;
    }

    /** @return $this */
    final public function whereMetaExists(string $metaKey)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'EXISTS']);
        return $this;
    }

    /** @return $this */
    public function whereMetaNotExists(string $metaKey)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'NOT EXISTS']);
        return $this;
    }

    /**
     * @param string $metaKey
     * @param 'ASC'|'DESC'|'' $direction
     * @return $this
     */
    final public function orderByMeta(string $metaKey, string $direction = '')
    {
        $this->queryVars['meta_key'] = $metaKey;
        $this->queryVars['orderby'] = 'meta_value';

        if ($direction) {
            $this->queryVars['order'] = $direction;
        }

        return $this;
    }

    /**
     * @param string $metaKey
     * @param 'ASC'|'DESC'|'' $direction
     * @return $this
     */
    final public function orderByMetaNum(string $metaKey, string $direction = '')
    {
        $this->queryVars['meta_key'] = $metaKey;
        $this->queryVars['orderby'] = 'meta_value_num';

        if ($direction) {
            $this->queryVars['order'] = $direction;
        }

        return $this;
    }

    final public function firstId(): ?int
    {
        return $this->limit(1)->ids()[0] ?? null;
    }

    final public function exists(): bool
    {
        return (bool)$this->firstId();
    }

    /** @return $this */
    final public function orderAsc()
    {
        $this->queryVars['order'] = 'ASC';
        return $this;
    }

    /** @return $this */
    final public function orderDesc()
    {
        $this->queryVars['order'] = 'DESC';
        return $this;
    }

    /** @return string Returns the last executed query as raw query string. */
    final public static function getLastRequest(): string
    {
        return self::$lastRequest;
    }
}
