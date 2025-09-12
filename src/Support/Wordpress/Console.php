<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Console\AbstractCommand;
use OffbeatWP\Content\Common\Singleton;
use WP_CLI;

final class Console extends Singleton
{
    /** @param AbstractCommand|class-string<AbstractCommand> $commandClass */
    public function register(AbstractCommand|string $commandClass): void
    {
        if (!self::isConsole()) {
            return;
        }

        $command = $commandClass::COMMAND;

        WP_CLI::add_command($command, function ($args, $argsNamed) use ($commandClass) {
            (new $commandClass())->execute($args, $argsNamed);
        });
    }

    public static function isConsole(): bool
    {
        return defined('WP_CLI') && constant('WP_CLI');
    }
}
