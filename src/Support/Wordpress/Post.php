<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Common\OwpSingleton;
use OffbeatWP\Content\Post\PostModel;
use WP_Post;

final class Post extends OwpSingleton
{
    public function convertWpPostToModel(WP_Post $post): PostModel
    {
        $model = PostType::getInstance()->getModelByPostType($post->post_type);
        return new $model($post);
    }

    public function get(WP_Post|int|null $id = null): ?PostModel
    {
        $post = get_post($id ?? get_the_ID() ?: null);

        if ($post) {
            return $this->convertWpPostToModel($post);
        }

        return null;
    }
}
