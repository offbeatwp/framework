<?php
namespace OffbeatWP\Contracts;

interface View
{
    public function render(string $template, array|object $data = []);
    public function registerGlobal(string $namespace, mixed $value): void;
    public function addTemplatePath(string $path): void;
}
