<?php

namespace OffbeatWP\Contracts;

interface View
{
    /** @param mixed[] $data */
    public function render(string $template, array $data = []): string;
    public function registerGlobal(string $namespace, mixed $value): void;
    public function addTemplatePath(string $path): void;
}
