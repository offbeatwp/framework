<?php
namespace OffbeatWP\Support\Wordpress;

class Page
{
    protected $pageTemplates = [];

    public function registerTemplate($label, $template)
    {
        $this->pageTemplates[$template] = $label;
    }

    public function getPageTemplates (): array
    {
        return $this->pageTemplates;
    }

}
