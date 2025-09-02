<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Console\AbstractCommand;
use OffbeatWP\Foundation\App;
use WP_CLI;

final class Console
{
    /** @param AbstractCommand|class-string<AbstractCommand> $commandClass */
    public function register(AbstractCommand|string $commandClass): void
    {
        if (!self::isConsole()) {
            return;
        }

        $command = $commandClass::COMMAND;

        WP_CLI::add_command($command, function ($args, $argsNamed) use ($commandClass) {
            App::singleton()->container->call([$commandClass, 'execute'], ['args' => $args, 'argsNamed' => $argsNamed]);
        });
    }

    public static function isConsole(): bool
    {
        return defined('WP_CLI') && constant('WP_CLI');
    }
}
