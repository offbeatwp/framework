<?php

namespace OffbeatWP\Form\Shared;

class Condition
{
    public static function valueIs(string $field, string $value): array
    {
        return ['field' => $field, 'operator' => '==', 'value' => $value];
    }

    public static function valueIsNot(string $field, string $value): array
    {
        return ['field' => $field, 'operator' => '!=', 'value' => $value];
    }

    public static function valueMatchesPattern(string $field, string $value): array
    {
        return ['field' => $field, 'operator' => '==pattern', 'value' => $value];
    }

    public static function valueMatchesContains(string $field, string $value): array
    {
        return ['field' => $field, 'operator' => '==contains', 'value' => $value];
    }

    public static function hasAnyValue(string $field): array
    {
        return ['field' => $field, 'operator' => '!=empty'];
    }

    public static function hasNoValue(string $field): array
    {
        return ['field' => $field, 'operator' => '==empty'];
    }
}