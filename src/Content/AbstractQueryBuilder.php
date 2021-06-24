<?php

namespace OffbeatWP\Content;

abstract class AbstractQueryBuilder {
    protected $queryVars = [];

    /**
     * @param string|string[]|null $orderBy
     * @param string|null $order 'ASC'|'DESC'
     * @return $this
     */
    public function order($orderBy = null, ?string $order = null): AbstractQueryBuilder {
        if (preg_match('/^(meta(_num)?):(.+)$/', $orderBy, $match)) {
            $this->queryVars['meta_key'] = $match[3];
            $this->queryVars['orderby'] = 'meta_value';

            if (isset($match[1]) && $match[1] == 'meta_num') {
                $this->queryVars['orderby'] = 'meta_value_num';
            }

        } elseif (!is_null($orderBy)) {
            $this->queryVars['orderby'] = $orderBy;
        }

        if (!is_null($order)) {
            $this->queryVars['order'] = $order;
        }

        return $this;
    }

    /**
     * This will execute a query!
     * Under most circumstances, you should use get() and then to a isNotEmpty check on the resulting collection.
     */
    public function exists(): bool
    {
        return $this->get()->isNotEmpty();
    }

    public function where(?array $parameters): AbstractQueryBuilder
    {
        $this->queryVars = array_merge($this->queryVars, $parameters);

        return $this;
    }

    /**
     * @param string|array $key
     * @param string|string[] $value
     * @param string $compare Possible values are ‘=’, ‘!=’, ‘>’, ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’, ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’, ‘NOT EXISTS’, ‘REGEXP’, ‘NOT REGEXP’ and ‘RLIKE’
     * @return $this
     */
    public function whereMeta($key, $value = '', string $compare = '='): AbstractQueryBuilder
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        if (is_array($key)) {
            $parameters = $key;
        } else {
            $parameters = ['key' => $key, 'value' => $value, 'compare' => $compare];
        }

        array_push($this->queryVars['meta_query'], $parameters);

        return $this;
    }
}