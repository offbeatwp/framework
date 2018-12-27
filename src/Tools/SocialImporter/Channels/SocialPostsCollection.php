<?php
namespace OffbeatWP\Tools\SocialImporter\Channels;

use Illuminate\Support\Collection;

class SocialPostsCollection extends Collection {
    public function __construct($items, $socialPostEntity) {
        foreach ($items as $item) {
            array_push($this->items, new $socialPostEntity($item));
        }
    }
}