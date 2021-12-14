<?php

namespace OffbeatWP\Form\Filters;

use OffbeatWP\Hooks\AbstractFilter;

class LoadFieldIconsFilter extends AbstractFilter {
    public function filter($field) {
        if (!isset($field['wrapper']['class']) || $field['wrapper']['class'] !== 'offbeat-icon-field') {
            return $field;
        }

        $iconsPattern = get_stylesheet_directory() . '/assets/icons/*.svg';

        $field['choices'] = ['' => ''];

        foreach (glob($iconsPattern) as $filename) {
            $basename = basename($filename, '.svg');
            if ($field['type'] === 'button_group') {
                $field['choices'][$basename] = "<i class='oif oif-{$basename}'></i>";
            } else {
                $field['choices'][$basename] = $basename;
            }
        }

        return $field;
    }
}