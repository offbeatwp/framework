<?php

namespace OffbeatWP\Support\Repository;

use OffbeatWP\Content\Post\PostModel;
use WP_Post;

final class PostRepository
{
    public static function getPostModel(WP_Post $post): PostModel
    {
        foreach (offbeat('post-type')->getPostTypeModels() as $modelType) {
            if ($modelType::is($post)) {
                return new $modelType($post);
            }
        }

        return new PostModel($post);
    }
}