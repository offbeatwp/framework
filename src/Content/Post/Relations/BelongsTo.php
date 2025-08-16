<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilderModel;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\BelongsToOneOrMany<T>
 */
class BelongsTo extends BelongsToOneOrMany
{
    /** @return WpQueryBuilderModel<T> */
    public function query()
    {
        return (new WpQueryBuilderModel($this->modelClass))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, 'reverse');
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
