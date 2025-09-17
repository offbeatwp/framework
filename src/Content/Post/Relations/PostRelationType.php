<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;

enum PostRelationType
{
    case HAS_ONE_OR_MANY;
    case BELONGS_TO_ONE_OR_MANY;

    final public function get(PostModel $post, string $key): HasOneOrMany|BelongsToOneOrMany
    {
        return match ($this) {
            self::HAS_ONE_OR_MANY => new HasOneOrMany($post, $key),
            self::BELONGS_TO_ONE_OR_MANY => new BelongsToOneOrMany($post, $key)
        };
    }
}