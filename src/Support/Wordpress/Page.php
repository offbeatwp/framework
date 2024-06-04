<?php
namespace OffbeatWP\Support\Wordpress;

final class Page
{
    /** @var string[] */
    protected $pageTemplates = [];

    /**
     * @param string $label
     * @param string $template
     * @return void
     */
    public function registerTemplate($label, $template)
    {
        $this->pageTemplates[$template] = $label;
    }

    /** @return string[] */
    public function getPageTemplates() {
        return $this->pageTemplates;
    }
}
