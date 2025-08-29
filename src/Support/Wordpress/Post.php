<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostModel;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @param PostModel $post
     * @param string[] $ignoreGetParameters
     * @return void
     */
    public function maybeRedirect(PostModel $post, array $ignoreGetParameters = [])
    {
        if (is_preview()) {
            return;
        }

        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
        $requestUri = strtok($request->getUri(), '?');
        $postUri = $post->getPermalink();

        if ($requestUri !== $postUri) {
            $url = $post->getPermalink();

            $getParameters = $_GET;
            foreach ($getParameters as $getParameterKey => $getParameter) {
                if (in_array($getParameterKey, $ignoreGetParameters)) {
                    unset($getParameters[$getParameterKey]);
                }
            }

            if ($getParameters) {
                $url .= '?' . http_build_query($getParameters);
            }

            offbeat('http')->redirect($url);
        }
    }
}
