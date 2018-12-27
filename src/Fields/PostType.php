<?php

namespace OffbeatWP\Fields;

class PostType {
    public static function get($name, $label, $attr = [])
    {
        $field               = $attr;
        $field['name']       = $name;
        $field['label']      = $label;
        $field['type']       = 'post_type';

        return [$field];
    }
}