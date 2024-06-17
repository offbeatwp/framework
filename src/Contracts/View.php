<?php

namespace OffbeatWP\Contracts;

interface View
{
    /**
     * @param string $template
     * @param mixed $data
     * @return string|null
     */
    public function render($template, $data = []);

    /**
     * @param string $namespace
     * @param mixed $value
     * @return mixed
     */
    public function registerGlobal($namespace, $value);

    /**
     * @param string $path
     * @return mixed
     */
    public function addTemplatePath($path);
}
