<?php
namespace OffbeatWP\Console;

abstract class AbstractCommand {
    abstract public function execute($args, $argsNamed);

    public function error($message) {
        \WP_CLI::error($message);
    }

    public function log($message) {
        \WP_CLI::log($message);
    }

    public function success($message) {
        \WP_CLI::success($message);
    }
}