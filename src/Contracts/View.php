<?php
namespace OffbeatWP\Contracts;

interface View
{
    public function render($template, $data = []);
    public function registerGlobal($namespace, $value);
    public function addTemplatePath($path);
}
