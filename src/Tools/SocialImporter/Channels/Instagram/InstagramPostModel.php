<?php
namespace OffbeatWP\Tools\SocialImporter\Channels\Instagram;

use OffbeatWP\Tools\SocialImporter\Models\SocialPostModel;

class InstagramPostModel extends SocialPostModel {
    public function getEmbed() {
        $embedCode = get_post_meta($this->getId(), 'embed', true);

        if (empty($embedCode)) {
            $embedCodeData = file_get_contents('https://api.instagram.com/oembed?url=' . $this->getLink());
            $embedCodeData = json_decode($embedCodeData);

            if (isset($embedCodeData->html)) {
                $embedCode = $embedCodeData->html;
                update_post_meta( $this->getId(), 'embed', $embedCodeData->html);
            }
        }

        $embedCode .= '<script>if (typeof window.instgrm !== \'undefined\') { window.instgrm.Embeds.process(); }</script>';

        return $embedCode;
    }
}