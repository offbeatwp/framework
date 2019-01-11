<?php
namespace OffbeatWP\Content\Post;

class WpQueryBuilderModel extends WpQueryBuilder
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;

        $this->wherePostType($model::POST_TYPE);

        $order = null;
        $orderDirection = null;

        if (defined("{$model}::ORDER_BY")) {
            $order = $model::ORDER_BY;
        }

        if (defined("{$model}::ORDER")) {
            $orderDirection = $model::ORDER;
        }

        $this->order($order, $orderDirection);
    }

    public function postToModel($post)
    {
        return new $this->model($post);
    }
}
