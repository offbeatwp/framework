<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use WP_Post;

/** @template TModel of PostModel */
class WpQueryBuilderModel extends WpQueryBuilder
{
    /** @var class-string<TModel> */
    protected $model;

    /**
     * @throws OffbeatInvalidModelException
     * @param class-string<TModel> $modelClass
     */
    public function __construct(string $modelClass)
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

    /** @return TModel|PostModel */
    public function firstOrNew(): PostModel
    {
        return $this->first() ?: new $this->model(null);
    }

    /**
     * @param WP_Post|int|null $post
     * @return TModel|PostModel|null
     */
    public function postToModel($post)
    {
        if ($this->model === PostModel::class) {
            return parent::postToModel($post);
        }

        return new $this->model($post);
    }

    /** @return TModel|null */
    public function first(): ?PostModel
    {
        return parent::first();
    }

    /** @return TModel */
    public function firstOrFail(): PostModel
    {
        return parent::firstOrFail();
    }
}
