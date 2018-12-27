<?php

namespace OffbeatWP\Fields;

class Terms {
    public static function get($name, $label, $taxonomies = [], $attr = [])
    {
        $field                = $attr;
        $field['name']        = $name;
        $field['label']       = $label;
        $field['type']        = 'terms';
        $field['taxonomies']  = $taxonomies;

        return $field;
    }
}