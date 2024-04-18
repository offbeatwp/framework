<?php
namespace OffbeatWP\Contracts;

interface View
{
    /**
     * @param string $template
     * @param mixed[]|object $data
     * @return string|null
     */
    public function render($template, $data = []): ?string;

    /**
     * @param string $namespace
     * @param mixed $value
     * @return void
     */
    public function registerGlobal($namespace, $value): void;

    /**
     * @param string $path
     * @return void
     */
    public function addTemplatePath($path): void;
}
