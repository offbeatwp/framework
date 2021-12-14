<?php

namespace OffbeatWP\Form\Filters;

use OffbeatWP\Hooks\AbstractFilter;

class LoadFieldIconsFilter extends AbstractFilter {
    public function filter($field) {
        if (!isset($field['wrapper']['class']) || $field['wrapper']['class'] !== 'iconfield') {
            return $field;
        }

        $iconsPattern = get_stylesheet_directory() . '/assets/icons/*.svg';

        $field['choices'] = ['' => ''];

        foreach (glob($iconsPattern) as $filename) {
            $iconName = basename($filename, '.svg');
            $field['choices'][$iconName] = $iconName;
        }

        return $field;
    }
}