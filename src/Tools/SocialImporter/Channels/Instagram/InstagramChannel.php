<?php
namespace OffbeatWP\Tools\SocialImporter\Channels\Instagram;

use OffbeatWP\Tools\SocialImporter\Channels\AbstractSocialChannel;
use OffbeatWP\Tools\SocialImporter\Channels\SocialChannelInterface;

class InstagramChannel extends AbstractSocialChannel implements SocialChannelInterface
{
    const API_URL      = 'https://api.instagram.com/';
    const ENTITY_CLASS = InstagramPostEntity::class;
    const MODEL_CLASS  = InstagramPostModel::class;

    public function getLoginUrl()
    {
        return $this->getUrl('oauth/authorize/?client_id=' . $this->config['api_key'] . '&redirect_uri=' . urlencode($this->getRedirectUrl('request_access_token')) . '&response_type=code');
    }

    public function requestAccessToken()
    {
        $data = [
            'client_id'     => $this->config['api_key'],
            'client_secret' => $this->config['api_secret'],
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->getRedirectUrl('request_access_token'),
            'code'          => $_GET['code'],
        ];

        $result = $this->request('oauth/access_token', 'POST', $data, false);

        if (isset($result->access_token)) {
            $this->setStatus('ready');
            return $result->access_token;
        }

        $this->setStatus('failed');
        return false;
    }

    public function requestPosts()
    {
        $results = $this->request('v1/users/self/media/recent/');

        return $this->convertToCollection($results->data);
    }
}
