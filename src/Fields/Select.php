<?php

namespace OffbeatWP\Fields;

class Select {
    public static function get($name, $label, $options = [], $attr = [])
    {
        $field              = $attr;
        $field['name']      = $name;
        $field['label']     = $label;
        $field['type']      = 'select';
        $field['options']   = $options;

        return $field;
    }
}