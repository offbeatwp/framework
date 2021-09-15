<?php

namespace OffbeatWP\Form\Shared;

class Condition
{
    /** @return array{field: string, operator: string, value: string} */
    public static function valueIs(string $field, string $value): array
    {
        return ['field' => $field, 'operator' => '==', 'value' => $value];
    }

    /** @return array{field: string, operator: string} */
    public static function hasAnyValue(string $field): array
    {
        return ['field' => $field, 'operator' => '!=empty'];
    }
}