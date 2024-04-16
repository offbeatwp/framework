<?php
namespace OffbeatWP\Components;

class GenericShortcode
{
    use ComponentInterfaceTrait;

    public string $componentClass;

    public function __construct(string $componentClass)
    {
        $this->componentClass = $componentClass;
    }

    public function renderShortcode(array $atts, string $content = ''): string
    {
        if ($content) {
            $atts['content'] = $content;
        }

        return $this->render(new ComponentSettings((object)$atts));
    }
}
