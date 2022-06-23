<?php

namespace OffbeatWP\Content\Traits;

trait OffbeatQueryTrait
{
    /** @return static */
    public function whereMetaIs(string $metaKey, $value)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => '==', 'value' => $value]);
        return $this;
    }

    /**
     * Get objects where the meta value is NOT equal to the given value.
     * @param non-empty-string $metaKey
     * @param scalar $value
     * @return static
     */
    public function whereMetaIsNot(string $metaKey, $value)
    {
        $this->whereMeta([
            'relation' => 'OR',
            ['key' => $metaKey, 'compare' => '!=', 'value' => $value],
            ['key' => $metaKey, 'compare' => 'NOT EXISTS']
        ]);

        return $this;
    }

    /** @return static */
    public function whereMetaIn(string $metaKey, array $values)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'IN', 'value' => $values]);
        return $this;
    }

    /** @return static */
    public function whereMetaExists(string $metaKey)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'EXISTS']);
        return $this;
    }

    /** @return static */
    public function whereMetaNotExists(string $metaKey)
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'NOT EXISTS']);
        return $this;
    }

    /**
     * @param string|string[]|null $orderBy
     * @param string|null $order 'ASC'|'DESC'
     * @return static
     */
    public function order($orderBy = null, ?string $order = null) {
        if (preg_match('/^(meta(_num)?):(.+)$/', $orderBy, $match)) {
            $this->queryVars['meta_key'] = $match[3];
            $this->queryVars['orderby'] = 'meta_value';

            if (isset($match[1]) && $match[1] === 'meta_num') {
                $this->queryVars['orderby'] = 'meta_value_num';
            }

        } elseif ($orderBy !== null) {
            $this->queryVars['orderby'] = $orderBy;
        }

        if ($order !== null) {
            $this->queryVars['order'] = $order;
        }

        return $this;
    }

    public function exists(): bool
    {
        return (bool)$this->first();
    }

    /** @return static */
    public function where(?array $parameters)
    {
        $this->queryVars = array_merge($this->queryVars, $parameters);

        return $this;
    }
}