<?php

namespace OffbeatWP\Fields;

class Text {
    public static function get($name, $label, $attr = [])
    {
        $field          = $attr;
        $field['name']  = $name;
        $field['label'] = $label;
        $field['type']  = 'text';

        return [$field];
    }
}