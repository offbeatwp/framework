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
     */
    abstract public function execute(array $args, array $argsNamed): mixed;

    final public function error(Exception|string|Throwable|WP_Error $message): void
    {
        WP_CLI::error($message);
    }

    final public function log(string $message): void
    {
        WP_CLI::log($message);
    }

    final public function success(string $message): void
    {
        WP_CLI::success($message);
    }
}