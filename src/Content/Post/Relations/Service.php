<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\Relations\Console\Install;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Form\Filters\LoadFieldIconsFilter;
use OffbeatWP\Services\AbstractService;
use WP_Query;

class Service extends AbstractService
{
    /** @return void */
    public function register() {
        add_filter('posts_clauses', [$this, 'insertRelationshipsSql'], 10, 2);

        if (offbeat('console')::isConsole()) {
            offbeat('console')->register(Install::class);
        }

        offbeat('hooks')->addFilter('acf/load_field', LoadFieldIconsFilter::class);
    }

    /**
     * @param string[] $clauses
     * @param WP_Query $query
     * @return string[]
     */
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

        if (is_array($query->query_vars['relationships']['id'] ?? null)) {
            $clauses['distinct'] = 'DISTINCT';
        }

        return $clauses;
    }

    /**
     * @param WP_Query $query
     * @return string[][]
     * @throws InvalidQueryOperatorException
     */
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

    /**
     * @param string[]|int[]|string[][]|int[][] $relationshipQuery
     * @param string $operator
     * @param int $n
     * @return string[]
     * @throws InvalidQueryOperatorException
     */
    private function buildQuery(array $relationshipQuery, string $operator = 'AND', int $n = 0): array
    {
        $this->checkOperator($operator);
        global $wpdb;
        $direction = null;

        if (!empty($relationshipQuery['direction'])) {
            $direction = $relationshipQuery['direction'];
        }

        $columnOn = 'relation_to';
        $columnWhere = 'relation_from';

        if ($direction === 'reverse') {
            $columnOn = 'relation_from';
            $columnWhere = 'relation_to';
        }

        if (is_array($relationshipQuery['id'])) {
            $ids = array_map('intval', $relationshipQuery['id']);
            $idQuery = 'IN (' . implode(', ', $ids) . ')';
        } else {
            $id = (int)$relationshipQuery['id'];
            $idQuery = "= {$id}";
        }

        return [
            'join' => " INNER JOIN {$wpdb->prefix}post_relationships AS pr{$n} ON ({$wpdb->posts}.ID = pr{$n}.{$columnOn}) ",
            'where' => " $operator pr{$n}.key = '{$relationshipQuery['key']}' AND pr{$n}.{$columnWhere} {$idQuery}"
        ];
    }

    /** @throws InvalidQueryOperatorException */
    private function checkOperator(?string $operator): void {
        if ($operator !== 'AND' && $operator !== 'OR') {
            throw new InvalidQueryOperatorException('Operator not valid for the relationships query builder. Only AND / OR are valid operators');
        }
    }
}
