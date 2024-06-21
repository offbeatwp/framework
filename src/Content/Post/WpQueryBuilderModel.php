<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use WP_Post;

/**
 * @template TValue of PostModel
 * @extends WpQueryBuilder<TValue>
 */
final class WpQueryBuilderModel extends WpQueryBuilder
{
    /** @var class-string<TValue> */
    protected string $modelClass;

    /**
     * @throws OffbeatInvalidModelException
     * @param class-string<TValue> $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        if ($modelClass::POST_TYPE) {
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

    /** @phpstan-return TValue|PostModel */
    public function firstOrNew(): PostModel
    {
        return $this->first() ?: new $this->modelClass(null);
    }

    /**
     * @param WP_Post|int|null $post
     * @phpstan-return TValue|PostModel|null
     */
    public function postToModel($post)
    {
        if ($this->modelClass === PostModel::class) {
            return parent::postToModel($post);
        }

        return new $this->modelClass($post);
    }

    /** @phpstan-return TValue|null */
    public function first(): ?PostModel
    {
        return parent::first();
    }

    /** @phpstan-return TValue */
    public function firstOrFail(): PostModel
    {
        return parent::firstOrFail();
    }
}
