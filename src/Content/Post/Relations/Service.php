<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Services\AbstractService;

class Service extends AbstractService
{
    public function register () {
        add_filter('posts_clauses', [$this, 'insertRelationshipsSql'], 10, 2 );

        if(offbeat('console')->isConsole()) {
            offbeat('console')->register(Console\Install::class);
        }
    }

    public function insertRelationshipsSql($clauses, $query) {
        if (!isset($query->query_vars['relationships']) || empty($query->query_vars['relationships'])) return $clauses;

        $relationshipSqlClauses = $this->getSqlClauses($query);
        foreach ($relationshipSqlClauses as $sql) {
            if (isset($sql['join']) && !empty($sql['join']))
                $clauses['join'] .= $sql['join'];

            if (isset($sql['where']) && !empty($sql['where']))
                $clauses['where'] .= $sql['where'];
        }

        return $clauses;
    }

    private function getSqlClauses($query) {
        $operator = $query->query_vars['relationships']['operator'] ?? false;
        if ($operator) {
            $multipleRelationships = $query->query_vars['relationships'];
            unset($multipleRelationships['operator']);
            foreach ($multipleRelationships as $relationship) {
                $sql[] = $this->buildQuery($relationship, $operator);
            }
        } else {
            //single relationship
            $sql[] = $this->buildQuery($query->query_vars['relationships']);
        }
        return $sql;
    }

    /**
     * @param $relationshipQuery
     *
     * @return array
     */
    private function buildQuery(array $relationshipQuery, $operator = 'AND'): array {
        $this->checkOperator($operator);
        global $wpdb;
        $direction = null;
        if (isset($relationshipQuery['direction']) && $relationshipQuery['direction']) {
            $direction = $relationshipQuery['direction'];
        }

        $columnOn = 'relation_to';
        $columnWhere = 'relation_from';

        if ($direction == 'reverse') {
            $columnOn = 'relation_from';
            $columnWhere = 'relation_to';
        }

        $sql = [];
        $sql['join'] = " INNER JOIN {$wpdb->prefix}post_relationships AS pr ON ({$wpdb->posts}.ID = pr.{$columnOn}) ";

        $sql['where'] = " $operator pr.key = '" . $relationshipQuery['key'] . "' AND pr.{$columnWhere} = " . $relationshipQuery['id'];

        return $sql;
    }

    private function checkOperator($operator) {
        if ($operator !== 'AND' && $operator !== 'OR') {
            throw new \Exception('Operator not valid for the relationships query builder');
        };
    }
}
