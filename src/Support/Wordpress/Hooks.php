<?php
namespace OffbeatWP\Support\Wordpress;

class Hooks
{
    public function addFilter(string $filter, callable $callback, int $priority = 10, int $acceptArguments = 1): void
    {
        add_filter($filter, static function (...$parameters) use ($callback) {
            if (is_string($callback)) {
                $callback = [$callback, 'filter'];
            }
                
            return container()->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    public function applyFilters(string $filter, ...$parameters)
    {
        return apply_filters_ref_array($filter, $parameters);
    }

    public function addAction(string $action, callable $callback, int $priority = 10, int $acceptArguments = 1): void
    {
        add_action($action, static function (...$parameters) use ($callback) {
            if (is_string($callback)) {
                $callback = [$callback, 'action'];
            }

            return container()->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    public function doAction(string $action, ...$args): void
    {
        do_action_ref_array($action, $args);
    }
}
