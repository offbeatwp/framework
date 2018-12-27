<?php
namespace OffbeatWP\Tools\SocialImporter\Channels\Instagram;

use OffbeatWP\Tools\SocialImporter\Channels\SocialPostEntity;

class InstagramPostEntity extends SocialPostEntity {

    public function __construct ($item) {
        $this->item = $item;
    }

    public function getEntityType() {
        return 'instagram';
    }

    public function getId() {
        return $this->item->id;
    }

    public function getImageUrl() {
        return $this->item->images->standard_resolution->url;
    }

    public function getText() {
        if (isset($this->item->caption)) {
            return $this->item->caption->text;
        }

        return '';
    }

    public function getTags() {
        return $this->item->tags;
    }

    public function getLink() {
        return $this->item->link;
    }

    public function getPublishedAt() {
        return $this->item->created_time;
    }

    public function getUser() {
        return $this->item->user;
    }

    public function getUserId() {
        return $this->getUser()->id;
    }

    public function getUserName() {
        return $this->getUser()->username;
    }

    public function getUserFullName() {
        return $this->getUser()->full_name;
    }

    public function getPostType() {
        if ($this->item->type == 'video') {
            return 'video';
        }
        return 'image';
    }
}