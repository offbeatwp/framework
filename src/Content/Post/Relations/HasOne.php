<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilderModel;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\HasOneOrMany<T>
 */
class HasOne extends HasOneOrMany
{
    /** @return \OffbeatWP\Content\Post\WpQueryBuilderModel<T> */
    public function query()
    {
        return (new WpQueryBuilderModel($this->modelClass))
            ->where(['ignore_sticky_posts' => 1])
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
