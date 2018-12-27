<?php

namespace OffbeatWP\Tools\GravityForms\Fields;

class Forms {
    public static function get($name, $label, $attr = [])
    {
        $field              = $attr;
        $field['name']      = $name;
        $field['label']     = $label;
        $field['type']      = 'select';
        $field['options']   = __NAMESPACE__ . '\Forms::options';

        return [$field];
    }

    public static function options()
    {
        $forms = \RGFormsModel::get_forms();

        $options = [];
        foreach ($forms as $form) {
            $options[$form->id] = $form->title;
        }

        return $options;
    }
}