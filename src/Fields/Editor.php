<?php
namespace OffbeatWP\Fields;

class Editor {
    public static function get($name, $label, $attr = [])
    {
        $field          = $attr;
        $field['name']  = $name;
        $field['label'] = $label;
        $field['type']  = 'editor';

        return $field;
    }
}