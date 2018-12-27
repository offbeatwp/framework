<?php

namespace OffbeatWP\Fields;

class Repeater {
    public static function get($name, $label, $fields, $attr = [])
    {
        $field              = $attr;
        $field['name']      = $name;
        $field['label']     = $label;
        $field['type']      = 'repeater';
        $field['fields']    = $fields;

        return [$field];
    }
}