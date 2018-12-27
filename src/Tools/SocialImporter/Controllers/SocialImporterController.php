<?php
namespace OffbeatWP\Tools\SocialImporter\Controllers;

use Illuminate\Support\Collection;
use OffbeatWP\Controllers\AbstractController;

class SocialImporterController extends AbstractController
{
    public function actionConfig()
    {
        $channels = raowApp('social_importer')->getChannels();

        $channels->each(function ($channel) {
            if (isset($_GET['channel_id']) && $_GET['channel_id'] == $channel->config('id')) {

                if ($channel->isStatus('request_access_token')) {
                    $accessToken = $channel->requestAccessToken();
                    if ($accessToken) {
                        $channel->saveAccessToken($accessToken);
                    }
                } elseif ($channel->isStatus('reset_channel')) {
                    $channel->clearAccessToken();
                }
            }
        });

        echo $this->render('config', ['channels' => $channels]);
    }
}
