<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;

/** @abstract */
class Relation
{
    protected $model;
    protected $relationKey;

    /**
     * @final
     * @param PostModel $model
     * @param string $relationKey
     */
    public function __construct($model, $relationKey)
    {
        $this->model = $model;
        $this->relationKey = $relationKey;
    }

    /** @return int|false */
    public function removeRelationship(int $id, ?string $direction = null)
    {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($direction === 'reverse') {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column1} = %d AND {$column2} = %d";
        $params = [$this->relationKey, $this->model->getId(), $id];

        return $wpdb->query($wpdb->prepare($query, $params));
    }

    /** @return int|false */
    public function removeAllRelationships(?string $direction = null)
    {
        global $wpdb;

        $column = 'relation_from';

        if ($direction === 'reverse') {
            $column = 'relation_to';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column} = %d";
        $params = [$this->relationKey, $this->model->getId()];

        return $wpdb->query($wpdb->prepare($query, $params));
    }

    /** @return int|false */
    public function makeRelationship(int $id, ?string $direction = null)
    {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($direction === 'reverse') {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        return $wpdb->insert(
            $wpdb->prefix . 'post_relationships',
            ['key' => $this->relationKey, $column1 => $this->model->getId(), $column2 => $id],
            ['%s', '%d', '%d']
        );
    }

    public function makeRelationships(iterable $ids, ?string $direction = null): void
    {
        foreach ($ids as $id) {
            $this->makeRelationship($id, $direction);
        }
    }
}
