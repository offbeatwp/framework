<?php
namespace OffbeatWP\Support\Wordpress;

final class Hooks
{
    /** @param callable(mixed...): mixed $callback */
    public function addFilter(string $filter, callable $callback, int $priority = 10, int $acceptArguments = 1): void
    {
        add_filter($filter, function (...$parameters) use ($callback) {
            if (is_string($callback)) {
                $callback = [$callback, 'filter'];
            }

            return container()->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    public function applyFilters(string $filter, mixed ...$parameters): mixed
    {
        return apply_filters_ref_array($filter, $parameters);
    }

    /** @param callable(mixed...): void $callback */
    public function addAction(string $action, callable $callback, int $priority = 10, int $acceptArguments = 1): void
    {
        add_action($action, function (...$parameters) use ($callback) {
            if (is_string($callback)) {
                $callback = [$callback, 'action'];
            }

            return container()->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    public function doAction(string $action, mixed ...$args): void
    {
        do_action_ref_array($action, $args);
    }
}
