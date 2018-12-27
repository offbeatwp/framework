<?php
namespace OffbeatWP\Tools\SocialImporter\Models;

use OffbeatWP\Content\Post\PostModel;

class SocialPostModel extends PostModel {
    const POST_TYPE = 'social-posts';

    public static function findBySocialPostId($socialPostId) {
        return self::where(['name' => $socialPostId])->first();
    }

    public function getLink() {
        return get_post_meta($this->getId(), 'link', true);
    }

    public function getType() {
        return get_post_meta($this->getId(), 'type', true);
    }

    public function getChannel() {
        return get_post_meta($this->getId(), 'channel', true);
    }

    public function getSocialId() {
        return get_post_meta($this->getId(), 'social_post_id', true);
    }

    public function getEmbed() { return null; }   
}