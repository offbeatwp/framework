<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostModel;
use WP_Post;

final class Post
{
    public function convertWpPostToModel(WP_Post $post): ?PostModel
    {
        $model = offbeat(PostType::class)->getModelByPostType($post->post_type);
        $model = offbeat(Hooks::class)->applyFilters('post_model', $model, $post);

        return new $model($post);
    }

    public function get(int|WP_Post $id = 0): ?PostModel
    {
        $post = get_post($id ?: get_the_ID());

        if ($post) {
            return $this->convertWpPostToModel($post);
        }

        return null;
    }
}
