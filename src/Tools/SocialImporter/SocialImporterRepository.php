<?php
namespace OffbeatWP\Tools\SocialImporter;

use Illuminate\Support\Collection;
use OffbeatWP\Tools\SocialImporter\Channels\Instagram\InstagramChannel;
use OffbeatWP\Tools\SocialImporter\Channels\Facebook\FacebookChannel;
use OffbeatWP\Tools\SocialImporter\Channels\SocialPostEntity;
use OffbeatWP\Tools\SocialImporter\Models\SocialPostModel;

class SocialImporterRepository
{
    public function getRecent($count = 15) {
        $socialPosts = SocialPostModel::take($count);

        return $socialPosts->map(function ($socialPost) {
            $channel = $this->getChannelClassByType($socialPost->getChannel());
            $model = $channel::MODEL_CLASS;

            return new $model($socialPost->wpPost);
        });
    }

    public function findById($id) {
        $socialPost = SocialPostModel::findById($id);

        if (!$socialPost) return null;

        return $this->convertModelToChanelModel($socialPost);
    }

    public function convertModelToChanelModel($socialPost)
    {
        $channel = $this->getChannelClassByType($socialPost->getType());
        $model = $channel::MODEL_CLASS;

        return new $model($socialPost->wpPost);
    }

    public function getChannelClassByType($type)
    {
        $channelClasses = [
            'instagram' => InstagramChannel::class,
            'facebook'  => FacebookChannel::class,
        ];

        if (!isset($channelClasses[$type])) {
            return null;
        }

        return $channelClasses[$type];
    }

    public function getChannels()
    {
        $channels = new Collection();

        $configChannels = config('social_importer');

        foreach ($configChannels as $configChannel) {
            $channelClass = $this->getChannelClassByType($configChannel['type']);

            if (is_null($channelClass)) {
                continue;
            }

            $channel = new $channelClass($configChannel);
            $channels->push($channel);
        };

        return $channels;
    }

    public function existsSocialPost(SocialPostEntity $post)
    {
        $posts = SocialPostModel::findBySocialPostId($post->getInternalId());

        if ($posts) {
            return true;
        }

        return false;
    }

    public function saveSocialPost(SocialPostEntity $post, $channel)
    {
        if ($this->existsSocialPost($post)) {
            return null;
        }

        if (is_null($post_status = $channel->config('post_status'))) {
            $post_status = 'publish';
        }

        $publishDate = date('Y-m-d H:i:s', $post->getPublishedAt());

        $postId = wp_insert_post([
            'post_type'     => SocialPostModel::POST_TYPE,
            'post_title'    => $post->getEntityType() . ' : ' . $post->getId(),
            'post_content'  => $post->getText(),
            'post_name'     => $post->getInternalId(),
            'post_date'     => get_date_from_gmt($publishDate),
            'post_date_gmt' => $publishDate,
            'post_status'   => 'publish',
            'meta_input'    => [
                'social_post_id'    => $post->getId(),
                'link'              => $post->getLink(),
                'channel'           => $post->getEntityType(),
                'type'              => $post->getPostType(),
            ],
        ]);

        if ($postId) {
            $attachmentId = media_sideload_image($post->getImageUrl(), $postId, null, 'id');
            set_post_thumbnail($postId, $attachmentId);
        }
    }

}
