<?php
namespace OffbeatWP\Contracts;

interface View
{
    /** @param mixed[]|object $data */
    public function render(string $template, array|object $data = []): ?string;
    public function registerGlobal(string $namespace, mixed $value): void;
    public function addTemplatePath(string $path): void;
}
