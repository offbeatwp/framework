<?php

namespace OffbeatWP\Support\Repository;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Support\Wordpress\PostType;
use WP_Post;

final class PostRepository
{
    public static function getPostModel(WP_Post $post): PostModel
    {
        foreach (offbeat(PostType::class)->getPostTypeModels() as $modelType) {
            if ($modelType::is($post)) {
                return new $modelType($post);
            }
        }

        return PostModel::from($post);
    }
}