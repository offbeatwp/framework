<?php

namespace OffbeatWP\Content\Post\Relations;

use InvalidArgumentException;
use OffbeatWP\Content\Post\Relations\Console\Install;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Form\Filters\LoadFieldIconsFilter;
use OffbeatWP\Services\AbstractService;
use WP_Query;

class Service extends AbstractService
{
    private const POST_FIELDS = ['ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count'];

    /** @return void */
    public function register()
    {
        add_filter('posts_clauses', [$this, 'insertRelationshipsSql'], 10, 2);
        add_filter('posts_clauses', [$this, 'insertFieldsSql'], 10, 2);

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
     * @param string[] $clauses
     * @param WP_Query $query
     * @return string[]
     */
    final public function insertFieldsSql(array $clauses, $query): array
    {
        if (!empty($query->query_vars['owp-fields']) && is_array($query->query_vars['owp-fields'])) {
            global $wpdb;

            $postFields = array_map(function (string $field) use ($wpdb) {
                if (!in_array($field, self::POST_FIELDS, true)) {
                    throw new InvalidArgumentException($field . ' is not a valid post field.');
                }

                return $wpdb->posts . '.' . $field;
            }, $query->query_vars['owp-fields']);

            $clauses['fields'] = implode(',', $postFields);
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
    private function checkOperator(?string $operator): void
    {
        if ($operator !== 'AND' && $operator !== 'OR') {
            throw new InvalidQueryOperatorException('Operator not valid for the relationships query builder. Only AND / OR are valid operators');
        }
    }
}
