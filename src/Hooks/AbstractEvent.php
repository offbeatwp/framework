<?php

namespace OffbeatWP\Hooks;

abstract class AbstractEvent
{
    public const EVENT_ACTION = 'action';
    public const EVENT_FILTER = 'filter';

    /** Either <i>action</i> or <i>filter</i>. */
    abstract public static function getEventType(): string;

    abstract public static function getEventName(): string;

    /**
     * @param callable $action The callback will receive an instance of this event as argument and <b>must</b> return an instance of this event as well.
     * @param int $priority sed to specify the order in which the functions associated with a particular event are executed. Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the filter.
     */
    public static function addEvent(callable $action, int $priority = 10): void
    {
        add_filter(static::getEventName(), $action, $priority);
    }

    /**
     * @param static $event
     * @return static
     */
    public static function doEvent($event)
    {
        return apply_filters(static::getEventName(), $event);
    }

    public static function removeEvent(callable $action, int $priority = 10): bool
    {
        return remove_filter(static::getEventName(), $action, $priority);
    }
}