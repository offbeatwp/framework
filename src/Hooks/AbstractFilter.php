<?php
namespace OffbeatWP\Hooks;

use OffbeatWP\Views\ViewableTrait;

abstract class AbstractFilter
{
    use ViewableTrait;

    public function __construct()
    {
        if (is_callable([$this, 'register'])) {
            container()->call([$this, 'register']);
        }
    }
}
