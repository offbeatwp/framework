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

    public function removeRelationship(int $id, bool $isReverse = false): ?int
    {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($isReverse) {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column1} = %d AND {$column2} = %d";
        $params = [$this->relationKey, $this->model->getId(), $id];

        $result = $wpdb->query($wpdb->prepare($query, $params));
        if ($result === false) {
            trigger_error('Failed to delete OWP relation from database.', E_USER_WARNING);
            return null;
        }

        return $result;
    }

    public function removeAllRelationships(bool $isReverse = false): ?int
    {
        global $wpdb;

        $column = 'relation_from';

        if ($isReverse) {
            $column = 'relation_to';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column} = %d";
        $params = [$this->relationKey, $this->model->getId()];

        $result = $wpdb->query($wpdb->prepare($query, $params));
        if ($result === false) {
            trigger_error('Failed to delete OWP relation from database.', E_USER_WARNING);
            return null;
        }

        return $result;
    }

    public function makeRelationship(int $id, bool $isReverse = false): ?int
    {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($isReverse) {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        $result =  $wpdb->insert(
            $wpdb->prefix . 'post_relationships',
            ['key' => $this->relationKey, $column1 => $this->model->getId(), $column2 => $id],
            ['%s', '%d', '%d']
        );

        if ($result === false) {
            trigger_error('Failed to delete OWP relation from database.', E_USER_WARNING);
            return null;
        }

        return $result;
    }

    /** @param iterable<int> $ids */
    public function makeRelationships(iterable $ids, bool $isReverse = false): void
    {
        foreach ($ids as $id) {
            $this->makeRelationship($id, $isReverse);
        }
    }
}
