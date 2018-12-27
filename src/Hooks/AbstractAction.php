<?php
namespace OffbeatWP\Hooks;

use OffbeatWP\Contracts\View;
use OffbeatWP\Views\ViewableTrait;

abstract class AbstractAction
{
    use ViewableTrait;

    public function __construct()
    {
        if (is_callable([$this, 'register'])) {
            container()->call([$this, 'register']);
        }
    }
}
