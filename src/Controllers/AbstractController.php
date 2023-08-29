<?php
namespace OffbeatWP\Controllers;

use OffbeatWP\Views\ViewableTrait;

abstract class AbstractController
{
    use ViewableTrait;

    /**
     * @param string $name
     * @param mixed[] $data
     * @return mixed
     */
    protected function render($name, $data = [])
    {
        $name = apply_filters('offbeatwp/controller/template', $name, $data);

        return $this->view($name, $data);
    }
}
