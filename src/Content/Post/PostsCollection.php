<?php
namespace OffbeatWP\Content\Post;

use Illuminate\Support\Collection;

class PostsCollection extends Collection
{
    public function __construct($items)
    {
        if ($items instanceof \WP_Query) {
            $postItems = [];

            foreach ($items->posts as $post) {
                array_push($postItems, raowApp('post')->convertWpPostToModel($post));
            }

            $items = $postItems;
            $postItems = null;
        } elseif (is_array($items)) {
            foreach ($items as $itemKey => $item) {
                if ($item instanceof \WP_Post) {
                    $items[$itemKey] = raowApp('post')->convertWpPostToModel($item);
                }
            }
        }

        parent::__construct($items);
    }

    public function getIterator()
    {
        return new WpPostsIterator($this->items);
    }
}
