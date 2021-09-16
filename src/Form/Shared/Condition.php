<?php

namespace OffbeatWP\Form\Shared;

class Condition
{
    public static function valueIs(string $name, string $value): array
    {
        return ['name' => $name, 'operator' => '==', 'value' => $value];
    }

    public static function valueIsNot(string $name, string $value): array
    {
        return ['name' => $name, 'operator' => '!=', 'value' => $value];
    }

    public static function valueMatchesPattern(string $name, string $value): array
    {
        return ['name' => $name, 'operator' => '==pattern', 'value' => $value];
    }

    public static function valueMatchesContains(string $name, string $value): array
    {
        return ['name' => $name, 'operator' => '==contains', 'value' => $value];
    }

    public static function hasAnyValue(string $name): array
    {
        return ['name' => $name, 'operator' => '!=empty'];
    }

    public static function hasNoValue(string $name): array
    {
        return ['name' => $name, 'operator' => '==empty'];
    }
}