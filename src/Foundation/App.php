<?php
namespace OffbeatWP\Foundation;

use DI\ContainerBuilder;

class App
{
    public static function singleton()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }


}
