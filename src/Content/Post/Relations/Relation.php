<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;

abstract class Relation
{
    protected readonly PostModel $model;
    protected readonly string $relationKey;

    final public function __construct(PostModel $model, string $relationKey)
    {
        $this->model = $model;
        $this->relationKey = $relationKey;
    }

    public function removeRelationship(int $id, bool $reverse = false): ?int
    {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($reverse) {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column1} = %d AND {$column2} = %d";
        $params = [$this->relationKey, $this->model->getId(), $id];

        $result = $wpdb->query($wpdb->prepare($query, $params));
        return $result === false ? null : $result;
    }

    public function removeAllRelationships(bool $reverse = false): ?int
    {
        global $wpdb;

        $column = 'relation_from';

        if ($reverse) {
            $column = 'relation_to';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column} = %d";
        $params = [$this->relationKey, $this->model->getId()];

        $result = $wpdb->query($wpdb->prepare($query, $params));
        return $result === false ? null : $result;
    }

    public function makeRelationship(int $id, bool $reverse = false): ?int
    {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($reverse) {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'post_relationships',
            ['key' => $this->relationKey, $column1 => $this->model->getId(), $column2 => $id],
            ['%s', '%d', '%d']
        );

        return $result === false ? null : $result;
    }

    /** @param iterable<int> $ids */
    public function makeRelationships(iterable $ids, bool $reverse = false): void
    {
        foreach ($ids as $id) {
            $this->makeRelationship($id, $reverse);
        }
    }

    /** @param int|int[] $ids */
    abstract public function attach(int|array $ids): void;
    abstract public function detach(int $id): void;
    abstract public function detachAll(): void;
}
