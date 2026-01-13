<?php

namespace OffbeatWP\Console;

use Exception;
use Throwable;
use WP_CLI;
use WP_Error;

abstract class AbstractCommand
{
    public const string COMMAND = '';

    /**
     * @param list<string> $args
     * @param array<string, string> $argsNamed
     */
    abstract public function execute(array $args, array $argsNamed): void;

    public function error(string|WP_Error|Exception|Throwable $message): never
    {
        WP_CLI::error($message);
    }

    public function log(string $message): void
    {
        WP_CLI::log($message);
    }

    public function success(string $message): void
    {
        WP_CLI::success($message);
    }
}
