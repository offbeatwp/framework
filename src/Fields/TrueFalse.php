<?php

namespace OffbeatWP\Fields;

class TrueFalse {
    public static function get($name, $label, $attr = [])
    {
        $field          = $attr;
        $field['name']  = $name;
        $field['label'] = $label;
        $field['type']  = 'true_false';

        return $field;
    }
}