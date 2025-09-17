<?php

namespace OffbeatWP\Content\Common;

abstract class OwpSingleton
{
    /** @var array<class-string<static>, static> */
    private static array $instances = [];

    final protected function __construct()
    {
    }

    final protected function __clone(): void
    {
    }

    final public function __wakeup(): void
    {
    }

    final public static function getInstance(): static
    {
        if (!array_key_exists(static::class, self::$instances)) {
            self::$instances[static::class] = new static();
        }

        return self::$instances[static::class];
    }
}
