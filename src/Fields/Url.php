<?php

namespace OffbeatWP\Fields;

class Url {
    public static function get($name, $label, $attr = [])
    {
        $field          = $attr;
        $field['name']  = $name;
        $field['label'] = $label;
        $field['type']  = 'link';

        return [$field];
    }
}