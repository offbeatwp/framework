<?php
namespace OffbeatWP\Tools\SocialImporter\Actions;

use OffbeatWP\Hooks\AbstractAction;

class SocialEmbed extends AbstractAction {
    function execute() {
        $postId = $_GET['post'];

        $post = raowApp('social_importer')->findById($postId);

        echo $post->getEmbed();
    }
}