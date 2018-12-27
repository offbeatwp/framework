<?php
namespace OffbeatWP\Tools\BeaverBuilder\ThirdParty;

class Polylang
{
    public static function filterFlAsPosts($posts)
    {
        if (!function_exists('pll_get_post_language') || $_GET['fl_as_action'] != 'fl_as_posts') {
            return $posts;
        }

        $newPosts = [];

        foreach ($posts as $post) {
            $lang = pll_get_post_language($post['value']);

            $suffix = '';
            if ($lang != '') {
                $suffix = ' (' . $lang . ')';
            }

            $newPosts[] = [
                'name'  => $post['name'] . $suffix,
                'value' => $post['value'],
            ];
        }

        if (!empty($newPosts)) {
            return $newPosts;
        }

        return $posts;

    }
}
