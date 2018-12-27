<?php
namespace OffbeatWP\Contracts;

interface View
{
    public function render($template, $data = []);
}