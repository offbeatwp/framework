<?php

namespace OffbeatWP\Fields;

class Textarea {
    public static function get($name, $label, $attr = [])
    {
        $field          = $attr;
        $field['name']  = $name;
        $field['label'] = $label;
        $field['type']  = 'textarea';

        return $field;
    }
}