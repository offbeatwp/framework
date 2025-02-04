<?php

namespace OffbeatWP\Console;

use Exception;
use Throwable;
use WP_CLI;
use WP_Error;

abstract class AbstractCommand
{
    public const COMMAND = '';

    /**
     * @param string[] $args
     * @param string[] $argsNamed
     * @return mixed
     */
    abstract public function execute(array $args, array $argsNamed);

    /**
     * @param Exception|string|Throwable|WP_Error $message
     * @return never-return
     */
    public function error($message)
    {
        WP_CLI::error($message);
        exit;
    }

    /**
     * @param string $message
     * @return void
     */
    public function log(string $message)
    {
        WP_CLI::log($message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function success(string $message)
    {
        WP_CLI::success($message);
    }
}
