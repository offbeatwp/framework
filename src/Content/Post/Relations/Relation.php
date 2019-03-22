<?php
namespace OffbeatWP\Content\Post\Relations;

class Relation
{
    protected $model;
    protected $key;

    public function __construct($model, $key)
    {
        $this->model = $model;
        $this->key   = $key;
    }

    public function removeRelationship($id, $direction = null) {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($direction == 'reverse') {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column1} = %d AND {$column2} = %d";
        $params = [
            $this->key,
            $model->getId(),
            $id
        ];

        $results = $wpdb->query($wpdb->prepare($query, $params));

        return $results;
    }

    public function removeAllRelationships($direction = null) {
        global $wpdb;

        $column = 'relation_from';

        if ($direction == 'reverse') {
            $column = 'relation_to';
        }

        $query = "DELETE FROM {$wpdb->prefix}post_relationships WHERE `key` = %s AND {$column} = %d";
        $params = [
            $this->key,
            $this->model->getId(),
        ];

        $results = $wpdb->query($wpdb->prepare($query, $params));

        return $results;
    }

    public function makeRelationship($id, $direction = null) {
        global $wpdb;

        $column1 = 'relation_from';
        $column2 = 'relation_to';

        if ($direction == 'reverse') {
            $column1 = 'relation_to';
            $column2 = 'relation_from';
        }

        return $wpdb->insert( 
            $wpdb->prefix . 'post_relationships', 
            [
                'key' => $this->key, 
                $column1 => $this->model->getId(),
                $column2 => $id
            ], 
            [ 
                '%s', 
                '%d',
                '%d'
            ] 
        );
    }

    public function makeRelationships($ids, $direction = null) {
        if (!empty($ids)) foreach ($ids as $id) {
            $this->makeRelationship($id, $direction);
        }
    }
}
