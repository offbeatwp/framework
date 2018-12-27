<?php
namespace OffbeatWP\Tools\SocialImporter\Channels;

abstract class AbstractSocialChannel
{
    public $config;
    protected $status = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function config($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return null;
    }

    public function getModel() {
        return static::MODEL_CLASS;
    }

    public function getId()
    {
        return $this->config('id');
    }

    public function getUrl($endpoint)
    {
        $url = static::API_URL . $endpoint;

        return $url;
    }

    public function getRedirectUrl($action = null)
    {
        $url = admin_url('/tools.php?page=social-importer&channel_id=' . $this->config('id'));

        if (!is_null($action)) {
            $url .= "&action={$action}";
        }

        return $url;
    }

    public function request($endpoint, $method = 'GET', $data = [], $authorizeRequest = true)
    {
        $url = $this->getUrl($endpoint);

        $ch = curl_init();

        $getParameters = [];

        if ($method == 'GET') {
            $getParameters = $data;
        } elseif ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true); // POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // POST DATA
        }

        if ($authorizeRequest) {
            $getParameters['access_token'] = $this->getAccessToken();
        }

        if (!empty($getParameters)) {
            $queryString     = http_build_query($getParameters);
            $queryStringGlue = '?';

            if (strpos($url, '?') !== false) {
                $queryStringGlue = '&';
            }

            $url .= $queryStringGlue . $queryString;
        }

        curl_setopt($ch, CURLOPT_URL, $url); // uri

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // RETURN RESULT true
        curl_setopt($ch, CURLOPT_HEADER, 0); // RETURN HEADER false
        curl_setopt($ch, CURLOPT_NOBODY, 0); // NO RETURN BODY false / we need the body to return
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // VERIFY SSL HOST false
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // VERIFY SSL PEER false

        $result = curl_exec($ch);

        return json_decode($result);
    }

    public function getResetUrl()
    {
        return $this->getRedirectUrl('reset_channel');
    }

    public function convertToCollection($items)
    {
        return new SocialPostsCollection($items, static::ENTITY_CLASS);
    }

    public function getAccessToken()
    {
        return get_option('socialChannelToken-' . $this->config('id'));
    }

    public function saveAccessToken($accessToken)
    {
        update_option('socialChannelToken-' . $this->config('id'), $accessToken);
    }

    public function clearAccessToken()
    {
        delete_option('socialChannelToken-' . $this->config('id'));

        $this->setStatus('not_configured');
    }

    public function getStatus()
    {
        if (!is_null($this->status)) {
            return $this->status;
        } elseif ($this->isAction('request_access_token')) {
            return 'request_access_token';
        } elseif ($this->isAction('reset_channel')) {
            return 'reset_channel';
        } elseif (!empty($this->getAccessToken())) {
            return 'ready';
        }

        return 'not_configured';
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function isStatus($status)
    {
        return $this->getStatus() == $status;
    }

    public function isNotStatus($status)
    {
        return !$this->isStatus($status);
    }

    public function isAction($action)
    {
        return isset($_GET['channel_id']) && $_GET['channel_id'] == $this->config('id') && $_GET['action'] == $action;
    }

    public function requestAndSavePosts()
    {
        $this->requestPosts()->each(function ($post) {
            raowApp('social_importer')->saveSocialPost($post, $this);
        });
    }

}
