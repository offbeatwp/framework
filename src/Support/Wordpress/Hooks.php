<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Foundation\App;

class Hooks
{
    /**
     * @param string $filter
     * @param callable(mixed...): mixed $callback
     * @param int $priority
     * @param int $acceptArguments
     * @return void
     */
    public function addFilter($filter, $callback, $priority = 10, $acceptArguments = 1)
    {
        add_filter($filter, function (...$parameters) use ($callback) {

            if (is_string($callback)) {
                $callback = [$callback, 'filter'];
            }
                
            return App::singleton()->container->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    /**
     * @param string $filter
     * @param mixed ...$parameters
     * @return mixed
     */
    public function applyFilters(string $filter, ...$parameters)
    {
        return apply_filters_ref_array($filter, $parameters);
    }

    /**
     * @param string $action
     * @param callable(mixed...): void $callback
     * @param int $priority
     * @param int $acceptArguments
     * @return void
     */
    public function addAction($action, $callback, $priority = 10, $acceptArguments = 1)
    {
        add_action($action, function (...$parameters) use ($callback) {
            if (is_string($callback)) {
                $callback = [$callback, 'action'];
            }

            return App::singleton()->container->call($callback, $parameters);
        }, $priority, $acceptArguments);
    }

    /**
     * @param string $action
     * @param mixed ...$args
     * @return void
     */
    public function doAction($action, ...$args)
    {
        do_action_ref_array($action, $args);
    }
}
