<?php
namespace OffbeatWP\Content\Post;

use Illuminate\Support\Collection;

class PostsCollection extends Collection
{
    protected $query = null;

    public function __construct($items)
    {
        if (is_object($items)) {
            $this->query = $items;

            $postItems = [];

            foreach ($items->posts as $post) {
                $postItems[] = offbeat('post')->convertWpPostToModel($post);
            }

            $items = $postItems;
            $postItems = null;
        } elseif (is_array($items)) {
            foreach ($items as $itemKey => $item) {
                if ($item instanceof \WP_Post) {
                    $items[$itemKey] = offbeat('post')->convertWpPostToModel($item);
                }
            }
        }

        parent::__construct($items);
    }

    public function getIterator()
    {
        return new WpPostsIterator($this->items);
    }

    public function getQuery() {
        return $this->query;
    }
}
