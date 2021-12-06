<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Hooks\AbstractAction;
use OffbeatWP\Hooks\AbstractFilter;

class Hooks
{
    /**
     * @param string $filter The name of the filter to add the callback to
     * @param class-string<AbstractFilter>|callable $callback The callback to be run when the filter is applied
     * @param int $priority Used to specify the order in which the functions associated with a particular filter are executed. Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the filter
     * @param int $acceptArguments The number of arguments the function accepts
     */
    public function addFilter(string $filter, $callback, int $priority = 10, int $acceptArguments = 1)
    {
        add_filter($filter, function (...$parameters) use ($callback) {
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

    /**
     * @param string $action The name of the filter to add the callback to
     * @param class-string<AbstractAction>|callable $callback The callback to be run when the filter is applied
     * @param int $priority Used to specify the order in which the functions associated with a particular filter are executed. Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the filter
     * @param int $acceptArguments The number of arguments the function accepts
     */
    public function addAction(string $action, $callback, int $priority = 10, int $acceptArguments = 1)
    {
        add_action($action, function (...$parameters) use ($callback) {
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
