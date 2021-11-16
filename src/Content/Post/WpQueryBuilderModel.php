<?php
namespace OffbeatWP\Content\Post;

class WpQueryBuilderModel extends WpQueryBuilder
{
    protected $model;

    /** @param class-string<PostModel> $model */
    public function __construct($model)
    {
        $this->model = $model;

        $this->wherePostType($model::POST_TYPE ?: 'any');

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

    /** @return PostModel */
    public function postToModel($post)
    {
        return new $this->model($post);
    }
}
