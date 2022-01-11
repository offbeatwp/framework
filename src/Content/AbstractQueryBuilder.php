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

    /**
     * This will execute a query!
     * Under most circumstances, you should use *all()* or *first()* and then do a isNotEmpty check on the resulting collection.
     */
    public function exists(): bool
    {
        return $this->all()->isNotEmpty();
    }

    public function where(?array $parameters): AbstractQueryBuilder
    {
        $this->queryVars = array_merge($this->queryVars, $parameters);

        return $this;
    }
}