<?php

namespace OffbeatWP\Support\Wordpress;

final class Page
{
    /** @var string[] */
    protected array $pageTemplates = [];

    public function registerTemplate(string $label, string $template): void
    {
        $this->pageTemplates[$template] = $label;
    }

    /** @return string[] */
    public function getPageTemplates(): array
    {
        return $this->pageTemplates;
    }
}
