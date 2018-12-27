<?php

namespace OffbeatWP\Components;

use OffbeatWP\Components\ComponentInterfaceTrait;
use OffbeatWP\Fields\Helper as FieldsHelper;

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
        $defaultAtts = FieldsHelper::getDefaults($form);

        if (!is_array($atts)) {
            $atts = [];
        }

        if (!empty($defaultAtts)) {
            $atts = array_merge($defaultAtts, $atts);
        }

        return $this->render((object) $atts);
    }
}