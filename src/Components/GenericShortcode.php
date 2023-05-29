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

    public function renderShortcode($atts, $content = '')
    {
        if (!is_array($atts)) {
            $atts = [];
        }

        if ($content) {
            $atts['content'] = $content;
        }

        return $this->render(new ComponentSettings((object)$atts));
    }
}
