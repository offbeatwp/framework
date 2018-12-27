<?php
namespace OffbeatWP\Tools\Acf\Hooks;

use OffbeatWP\Hooks\AbstractFilter;

class AcfTermAttributeFilter extends AbstractFilter {
    public function filter ($value, $name, $model) {
        if (!empty($fieldValue = get_field($name, $model->wpTerm))) {
            return $fieldValue;
        }

        return $value;
    }
}