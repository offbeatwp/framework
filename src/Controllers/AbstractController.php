<?php
namespace OffbeatWP\Controllers;

use OffbeatWP\Contracts\View;
use OffbeatWP\Views\ViewableTrait;

abstract class AbstractController
{
    use ViewableTrait;

    protected function render($name, $data = [])
    {
        return $this->view($name, $data);
    }
}
