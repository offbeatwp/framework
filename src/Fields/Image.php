<?php

namespace OffbeatWP\Fields;

class Image {
    public static function get($name, $label, $attr = [])
    {
        $field          = $attr;
        $field['name']  = $name;
        $field['label'] = $label;
        $field['type']  = 'image';

        return [$field];
    }
}