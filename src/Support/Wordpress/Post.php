<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostModel;
use Symfony\Component\HttpFoundation\Request;
use WP_Post;

class Post
{
    /** @return PostModel|null */
    public function convertWpPostToModel(WP_Post $post)
    {
        $model = offbeat('post-type')->getModelByPostType($post->post_type);
        $model = offbeat('hooks')->applyFilters('post_model', $model, $post);

        return new $model($post);
    }

    /**
     * @param null|int|WP_Post $id
     * @return PostModel|null
     */
    public function get($id = null)
    {
        if (is_null($id)) {
            $id = get_the_ID();
        }

        $post = get_post($id);

        if (!empty($post)) {
            return $this->convertWpPostToModel($post);
        }

        return null;
    }

    public function maybeRedirect($post)
    {
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
        $requestUri = strtok($request->getUri(), '?');
        $postUri    = $post->getPermalink();

        if ($requestUri !== $postUri) {
            $url = $post->getPermalink();

            if (!empty($_GET))
                $url .= '?' . http_build_query($_GET);

            offbeat('http')->redirect($url);
            exit;
        }
    }
}
