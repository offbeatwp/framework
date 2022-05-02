<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use WP_Post;

class WpQueryBuilderModel extends WpQueryBuilder
{
    protected $model;

    /**
     * @throws OffbeatInvalidModelException
     * @param class-string<PostModel> $modelClass
     */
    final public function __construct(string $modelClass)
    {
        $this->model = $modelClass;

        if (defined("{$modelClass}::POST_TYPE")) {
            $this->wherePostType($modelClass::POST_TYPE);
        }

        $order = null;
        $orderDirection = null;

        if (defined("{$modelClass}::ORDER_BY")) {
            $order = $modelClass::ORDER_BY;
        }

        if (defined("{$modelClass}::ORDER")) {
            $orderDirection = $modelClass::ORDER;
        }

        $this->order($order, $orderDirection);
    }

    public function firstOrNew(): PostModel
    {
        return $this->first() ?: new $this->model(null);
    }

    /**
     * @param WP_Post|int|null $post
     * @return PostModel
     */
    public function postToModel($post)
    {
        if ($this->model === PostModel::class) {
            return parent::postToModel($post);
        }

        return new $this->model($post);
    }
}
