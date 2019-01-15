<?php
namespace OffbeatWP\Content\Post\Relations\Console;

use OffbeatWP\Console\AbstractCommand;

class Install extends AbstractCommand
{

    public function execute($args, $argsNamed)
    {
        global $wpdb;

        $wpdb->query("
CREATE TABLE `{$wpdb->prefix}posts_relationships` (
  `relation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT '',
  `relation_from` int(11) NOT NULL,
  `relation_to` int(11) NOT NULL,
  PRIMARY KEY (`relation_id`),
  KEY `relation_from` (`relation_from`),
  KEY `relation_to` (`relation_to`),
  KEY `key_from` (`key`,`relation_from`),
  KEY `key_to` (`key`,`relation_to`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

        $this->success('Table installed');
    }

}
