<?php
namespace OffbeatWP\Tools\SocialImporter\Console;

use OffbeatWP\Console\AbstractCommand;

class SocialImporterCommand extends AbstractCommand {
    const COMMAND = 'social-importer';

    protected $dryRun = false;

    public function execute($args, $argsNamed)
    {
        if (isset($argsNamed['dry-run']) && $argsNamed['dry-run']) {
            $this->dryRun = true;
        }

        $channels = offbeat('social_importer')->getChannels();

        $channels->each(function ($channel) {
            if($channel->isNotStatus('ready')) {
                $this->error('Channel not ready: ' . $channel->getId());
                return null;
            }

            $this->log('Start importing: ' . $channel->getId());
            $this->importPosts($channel);
            $this->success('Finished importing: ' . $channel->getId());
        });
    }

    public function importPosts ($channel) {
        $channel->requestAndSavePosts();
    }
}