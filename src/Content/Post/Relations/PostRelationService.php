<?php

namespace OffbeatWP\Content\Post\Relations;

use InvalidArgumentException;
use OffbeatWP\Content\Post\Relations\Console\Install;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Support\Wordpress\Console;
use OffbeatWP\Support\Wordpress\Post;
use UnexpectedValueException;
use WP_Query;

final class PostRelationService extends AbstractService
{
    private const array POST_FIELDS = ['ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count'];

    public function register(): void
    {
        add_filter('posts_clauses', [$this, 'insertRelationshipsSql'], 10, 2);
        add_filter('posts_clauses', [$this, 'insertFieldsSql'], 10, 2);
        add_action('updated_post_meta', [$this, 'updatedPostMeta'], 10, 4);

        Console::getInstance()->register(Install::class);
    }

    public function updatedPostMeta(int $metaId, int $postId, string $metaKey, mixed $value): void
    {
        $post = Post::getInstance()->get($postId);
        if (!$post) {
            return;
        }

        $method = $post->getMethodByRelationKey($metaKey);
        if (!$method || !is_callable([$post, $method])) {
            return;
        }

        /** @var \OffbeatWP\Content\Post\Relations\HasOneOrMany|\OffbeatWP\Content\Post\Relations\BelongsToOneOrMany $relation */
        $relation = $post->$method();

        /** @var scalar|null|array<scalar|null> $value */
        if ($value) {
            if (is_array($value)) {
                $relationshipIds = array_map('intval', $value);
            } else {
                $relationshipIds = [(int)$value];
            }

            if ($relation instanceof HasOneOrMany) {
                $relation->attach($relationshipIds);
            } else {
                $relation->attach($relationshipIds);
            }
        } elseif ($relation instanceof HasOneOrMany) {
            $relation->detachAll();
        } else {
            $relation->detachAll();
        }
    }

    /**
     * @param string[] $clauses
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
     * @return string[]
     */
    final public function insertFieldsSql(array $clauses, WP_Query $query): array
    {
        if (!empty($query->query_vars['owp-fields']) && is_array($query->query_vars['owp-fields'])) {
            global $wpdb;

            $fields = array_map(function ($field) use ($wpdb) {
                if (!in_array($field, self::POST_FIELDS, true)) {
                    throw new InvalidArgumentException('Passed OWP field is not a valid post field.');
                }

                return $wpdb->posts . '.' . $field;
            }, $query->query_vars['owp-fields']);

            $clauses['fields'] = implode(',', $fields);
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
     * @param mixed[] $relationshipQuery
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
            $ids = $this->mapIds($relationshipQuery['id']);
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

    /**
     * @param mixed[] $rawIds
     * @return list<non-negative-int>
     */
    private function mapIds(array $rawIds): array
    {
        $output = [];

        foreach ($rawIds as $rawId) {
            $id = filter_var($rawId, FILTER_VALIDATE_INT);

            if (!is_int($id) || $id < 0) {
                throw new UnexpectedValueException('Post Relationship ID is not a positive integer.');
            }

            $output[] = $id;
        }

        return $output;
    }

    /** @throws InvalidQueryOperatorException */
    private function checkOperator(?string $operator): void
    {
        if ($operator !== 'AND' && $operator !== 'OR') {
            throw new InvalidQueryOperatorException('Operator not valid for the relationships query builder. Only AND / OR are valid operators');
        }
    }
}
