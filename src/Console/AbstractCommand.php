<?php
namespace OffbeatWP\Console;

use Exception;
use Throwable;
use WP_CLI;
use WP_Error;

abstract class AbstractCommand {
    /**
     * @param string[] $args
     * @param string[] $argsNamed
     * @return mixed
     */
    abstract public function execute($args, $argsNamed);

    /**
     * @param Exception|string|Throwable|WP_Error $message
     * @return void
     */
    public function error($message)
    {
        WP_CLI::error($message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function log($message)
    {
        WP_CLI::log($message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function success($message)
    {
        WP_CLI::success($message);
    }
}