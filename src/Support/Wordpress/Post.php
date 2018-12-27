<?php
namespace OffbeatWP\Support\Wordpress;

class Post
{
    public function convertWpPostToModel(\WP_Post $post) {
        $model = raowApp('post-type')->getModelByPostType($post->post_type);
        $model = raowApp('hooks')->applyFilters('post_model', $model, $post);

        return new $model($post);
    }

    public function get($id = null) {
        if (is_null($id)) $id = get_the_ID();

        $post = get_post($id);

        if (!empty($post)) {
            return $this->convertWpPostToModel($post);
        }

        return null;
    }
}
