<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use RuntimeException;

abstract class AbstractRelation
{
    protected PostModel|string $model;
    protected string $relationKey;

    /**
     * @param PostModel|class-string<PostModel> $model
     * @param non-empty-string $relationKey
     */
    final public function __construct(PostModel|string $model, string $relationKey)
    {
        $this->model = $model;
        $this->relationKey = $relationKey;
    }

    final public function removeRelationship(int $id, bool $inverted = false): int
    {
        global $wpdb;

        if ($inverted) {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        } else {
            $column1 = 'relation_from';
            $column2 = 'relation_to';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column1} = %d AND {$column2} = %d";
        $params = [$this->relationKey, $this->model->getId(), $id];
        $result = $wpdb->query($wpdb->prepare($query, $params));

        if ($result === false) {
            throw new RuntimeException('The removeRelationship query failed to run.');
        }

        return $result;
    }

    final public function removeAllRelationships(bool $inverted = false): int
    {
        global $wpdb;

        $column = ($inverted) ? 'relation_to' : 'relation_from';
        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column} = %d";
        $params = [$this->relationKey, $this->model->getId()];
        $result = $wpdb->query($wpdb->prepare($query, $params));

        if ($result === false) {
            throw new RuntimeException('The removeAllRelationships query failed to run.');
        }

        return $result;
    }

    final public function makeRelationship(int $id, bool $inverted = false): int
    {
        global $wpdb;

        if ($inverted) {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        } else {
            $column1 = 'relation_from';
            $column2 = 'relation_to';
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'post_relationships',
            ['key' => $this->relationKey, $column1 => $this->model->getId(), $column2 => $id],
            ['%s', '%d', '%d']
        );

        if ($result === false) {
            throw new RuntimeException('The makeRelationship query failed to run.');
        }

        return $result;
    }

    /** @param iterable<int> $ids */
    final public function makeRelationships(iterable $ids, bool $inverted = false): void
    {
        foreach ($ids as $id) {
            $this->makeRelationship($id, $inverted);
        }
    }
}
