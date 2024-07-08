<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\HasOneOrMany<T>
 */
class HasOne extends HasOneOrMany
{
    /** @phpstan-return WpQueryBuilder<T> */
    public function query()
    {
        /** @var WpQueryBuilder<T> $builer */
        $builer = new WpQueryBuilder();

        return $builer
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType($this->model::POST_TYPE)
            ->hasRelationshipWith($this->model, $this->relationKey);
    }

    /**
     * @return \OffbeatWP\Content\Post\PostModel|null
     * @phpstan-return T|null
     */
    public function get()
    {
        return $this->query()->first();
    }
}
