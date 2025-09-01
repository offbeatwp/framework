<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostModel;
use WP_Post;

final class Post
{
    public function convertWpPostToModel(WP_Post $post): PostModel
    {
        $model = offbeat('post-type')->getModelByPostType($post->post_type);
        $model = apply_filters_ref_array('post_model', [$model, $post]);

        return new $model($post);
    }

    /** @param null|int|numeric-string|WP_Post $id */
    public function get($id = null): ?PostModel
    {
        $post = get_post($id ?? get_the_ID());

        if ($post) {
            return $this->convertWpPostToModel($post);
        }

        return null;
    }
}
