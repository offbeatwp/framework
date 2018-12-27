<?php
namespace OffbeatWP\Tools\SocialImporter\Channels;

interface SocialChannelInterface {
    public function getLoginUrl();

    public function requestAccessToken();

    public function requestPosts();
}