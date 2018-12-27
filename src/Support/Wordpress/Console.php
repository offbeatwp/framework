<?php
namespace OffbeatWP\Support\Wordpress;

class Console
{
    public function register($commandClass)
    {
        if (!self::isConsole()) return null;

        $command = $commandClass::COMMAND;

        \WP_CLI::add_command($command, function ($args, $argsNamed) use ($commandClass) {
            container()->call([$commandClass, 'execute'], ['args' => $args, 'argsNamed' => $argsNamed]);
        });
    }

    public static function isConsole()
    {
        return (defined('WP_CLI') && \WP_CLI);
    }
}
