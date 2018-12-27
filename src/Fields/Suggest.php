<?php

namespace OffbeatWP\Fields;

class Suggest
{
    public static function get($name, $label, $action, $data, $attr = [])
    {
        $field           = $attr;
        $field['name']   = $name;
        $field['label']  = __($label, 'raow');
        $field['type']   = 'suggest';
        $field['action'] = $action;
        $field['data']   = $data;

        return $field;
    }
}
