<?php
namespace OffbeatWP\Tools\SocialImporter\Channels\Facebook;

use OffbeatWP\Tools\SocialImporter\Channels\SocialPostEntity;

class FacebookPostEntity extends SocialPostEntity {

    public function __construct ($item) {
        $this->item = $item;
    }

    public function getEntityType() {
        return 'facebook';
    }

    public function getId() {
        return $this->item->id;
    }

    public function getImageUrl() {
        if (isset($this->item->full_picture)) {
            return $this->item->full_picture;    
        }
        
        return null;
    }

    public function getText() {
        if (isset($this->item->message)) {
            return $this->item->message;
        }

        return '';
    }

    public function getTags() {
        return null;
    }

    public function getLink() {
        return $this->item->link;
    }

    public function getPublishedAt() {
        return strtotime($this->item->created_time);
    }

    public function getUser() {
        return null;
    }

    public function getUserId() {
        return null;
    }

    public function getUserName() {
        return null;
    }

    public function getUserFullName() {
        return null;
    }

    public function getPostType() {
        if ($this->item->type == 'video') {
            return 'video';
        }
        return 'image';
    }
}