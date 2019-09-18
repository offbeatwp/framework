<?php
namespace OffbeatWP\Components;

use OffbeatWP\Components\ComponentInterfaceTrait;

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
        $form = $this->componentClass::getForm();

        if (!is_array($atts)) {
            $atts = [];
        }

        if (!empty($content)) {
            $atts['content'] = $content;
        }

        if (!empty($defaultAtts)) {
            $atts = array_merge($defaultAtts, $atts);
        }

        return $this->render((object) $atts);
    }
}