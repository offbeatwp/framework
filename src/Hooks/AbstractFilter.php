<?php
namespace OffbeatWP\Hooks;

use OffbeatWP\Foundation\App;
use OffbeatWP\Views\ViewableTrait;

abstract class AbstractFilter
{
    use ViewableTrait;

    public function __construct()
    {
        if (is_callable([$this, 'register'])) {
            App::singleton()->container->call([$this, 'register']);
        }
    }
}
