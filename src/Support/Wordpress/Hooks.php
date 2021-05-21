<?php
namespace OffbeatWP\Support\Wordpress;

class Hooks
{
    public function addFilter($filter, $callback, $priority = 10, $acceptArguments = 1)
    {
        add_filter($filter, function (...$parameters) use ($callback) {

            if (is_string($callback)) {
                $callback = [$callback, 'filter'];
            }
                
            return container()->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    /**
     * @param string $filter
     * @param ...$parameters
     * @return string
     */
    public function applyFilters($filter, ...$parameters)
    {
        return apply_filters_ref_array($filter, $parameters);
    }

    public function addAction($action, $callback, $priority = 10, $acceptArguments = 1)
    {
        add_action($action, function (...$parameters) use ($callback) {
            if (is_string($callback)) {
                $callback = [$callback, 'action'];
            }

            return container()->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    public function doAction($action, ...$args)
    {
        do_action_ref_array($action, $args);
    }
}
