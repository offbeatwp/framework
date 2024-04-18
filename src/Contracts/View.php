<?php
namespace OffbeatWP\Contracts;

interface View
{
    /**
     * @param string $template
     * @param mixed[]|object $data
     * @return string|null
     */
    public function render($template, $data = []);

    /**
     * @param string $namespace
     * @param mixed $value
     * @return void
     */
    public function registerGlobal($namespace, $value);

    /**
     * @param string $path
     * @return void
     */
    public function addTemplatePath($path);
}
