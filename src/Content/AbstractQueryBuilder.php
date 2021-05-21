<?php

namespace OffbeatWP\Content;

abstract class AbstractQueryBuilder {
    protected $queryVars = [];

    protected function orderQueryVars($orderBy = null, $direction = null) {
        if (preg_match('/^(meta(_num)?):(.+)$/', $orderBy, $match)) {
            $this->queryVars['meta_key'] = $match[3];
            $this->queryVars['orderby'] = 'meta_value';

            if (isset($match[1]) && $match[1] == 'meta_num') {
                $this->queryVars['orderby'] = 'meta_value_num';
            }

        } elseif (!is_null($orderBy)) {
            $this->queryVars['orderby'] = $orderBy;
        }

        if (!is_null($direction)) {
            $this->queryVars['order'] = $direction;
        }
    }
}