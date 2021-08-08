<?php
namespace OffbeatWP\Components;

class GenericShortcode
{
    use ComponentInterfaceTrait;

    public $componentClass = null;

    public function __construct($componentClass)
    {
        $this->componentClass = $componentClass;
    }

    public function renderShortcode($atts, $content = "")
    {
        if (!is_array($atts)) {
            $atts = [];
        }

        if (!empty($content)) {
            $atts['content'] = $content;
        }

        return $this->render((object) $atts);
    }
}