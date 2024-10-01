<?php

namespace OffbeatWP\Components;

class GenericShortcode
{
    use ComponentInterfaceTrait;

    /** @var class-string|null */
    public $componentClass = null;

    /** @param class-string $componentClass */
    public function __construct($componentClass)
    {
        $this->componentClass = $componentClass;
    }

    /**
     * @param mixed[]|null $atts
     * @param string $content
     * @return string|null
     */
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
