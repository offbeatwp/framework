<?php
namespace OffbeatWP\Tools\SocialImporter\Channels\Facebook;

use OffbeatWP\Tools\SocialImporter\Models\SocialPostModel;

class FacebookPostModel extends SocialPostModel {
    public function getEmbed()
    {
        $postId = $this->getSocialId();
        $userId = $this->getSocialUserId();

        return `<div class="fb-post" data-href="https://www.facebook.com/${postId}/posts/${userId}/" data-width="500" data-show-text="true"></div>`;
    }

    public function getSocialUserId() {
        return ''; //TODO
    }
}