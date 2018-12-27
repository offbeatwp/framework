<?php

namespace OffbeatWP\Fields;

class Posts {
    public static function get($name, $label, $postTypes = [], $attr = [])
    {
        $field               = $attr;
        $field['name']       = $name;
        $field['label']      = $label;
        $field['type']       = 'posts';
        $field['postTypes']  = $postTypes;

        return [$field];
    }
}