<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\Relations\Console\Install;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Services\AbstractService;
use WP_Query;

class Service extends AbstractService
{
    public function register () {
        add_filter('posts_clauses', [$this, 'insertRelationshipsSql'], 10, 2 );

        if(offbeat('console')->isConsole()) {
            offbeat('console')->register(Install::class);
        }
    }

    /** @throws InvalidQueryOperatorException */
    public function insertRelationshipsSql(array $clauses, WP_Query $query): array
    {
        if (empty($query->query_vars['relationships'])) {
            return $clauses;
        }

        $relationshipSqlClauses = $this->getSqlClauses($query);
        foreach ($relationshipSqlClauses as $sql) {
            if (!empty($sql['join'])) {
                $clauses['join'] .= $sql['join'];
            }

            if (!empty($sql['where'])) {
                $clauses['where'] .= $sql['where'];
            }
        }

        return $clauses;
    }

    /** @throws InvalidQueryOperatorException */
    private function getSqlClauses(WP_Query $query): array
    {
        $sql = [];
        $operator = $query->query_vars['relationships']['operator'] ?? null;

        if ($operator) {
            $multipleRelationships = $query->query_vars['relationships'];
            unset($multipleRelationships['operator']);
            $n = 0;

            foreach ($multipleRelationships as $relationship) {
                $sql[] = $this->buildQuery($relationship, $operator, $n);
                $n++;
            }
        } else {
            //single relationship
            $sql[] = $this->buildQuery($query->query_vars['relationships']);
        }

        return $sql;
    }

    /** @throws InvalidQueryOperatorException */
    private function buildQuery(array $relationshipQuery, string $operator = 'AND', int $n = 0): array
    {
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
        $sql['join'] = " INNER JOIN {$wpdb->prefix}post_relationships AS pr{$n} ON ({$wpdb->posts}.ID = pr{$n}.{$columnOn}) ";

        $sql['where'] = " $operator pr{$n}.key = '" . $relationshipQuery['key'] . "' AND pr{$n}.{$columnWhere} = " . $relationshipQuery['id'];

        return $sql;
    }

    /** @throws InvalidQueryOperatorException */
    private function checkOperator(?string $operator) {
        if ($operator !== 'AND' && $operator !== 'OR') {
            throw new InvalidQueryOperatorException('Operator not valid for the relationships query builder. Only AND / OR are valid operators');
        };
    }
}
