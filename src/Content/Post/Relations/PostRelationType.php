<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;

enum PostRelationType
{
    case BELONGS_TO_ONE;
    case BELONGS_TO_MANY;
    case HAS_ONE;
    case HAS_MANY;

    final public function get(PostModel $post, string $relationKey): BelongsToOne|BelongsToMany|HasOne|HasMany
    {
        return match ($this) {
            self::BELONGS_TO_ONE => new BelongsToOne($post, $relationKey),
            self::BELONGS_TO_MANY => new BelongsToMany($post, $relationKey),
            self::HAS_ONE => new HasOne($post, $relationKey),
            self::HAS_MANY => new HasMany($post, $relationKey)
        };
    }
}