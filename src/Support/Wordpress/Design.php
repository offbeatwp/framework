<?php
namespace OffbeatWP\Support\Wordpress;

use Closure;
use function _PHPStan_5473b6701\RingCentral\Psr7\str;

final class Design
{
    public function getRowThemeClasses(string $id): mixed
    {
        $rowThemes  = config('design.row_themes');
        if (!$rowThemes) {
            return null;
        }

        $subId = null;

        if (str_contains($id, '**')) {
            [$id, $subId] = explode('**', $id);
        }

        if (!isset($rowThemes[$id])) {
            return null;
        }

        $classes = $rowThemes[$id]['classes'];

        if ($subId !== null && isset($rowThemes[$id]['sub_themes'][$subId])) {
            $classes .= ' ' . $rowThemes[$id]['sub_themes'][$subId]['classes'];
        }

        return $classes;
    }

    public function getMarginClasses(string $id, string $context, string $prefix): ?string
    {
        $margins = config('design.margins');
        if (!$margins) {
            return null;
        }

        $margins = $margins($context);

        if (!isset($margins[$id])) {
            return null;
        }

        return str_replace('{{prefix}}', $prefix, $margins[$id]['classes']);
    }

    public function getPaddingClasses(string $id, string $context, string $prefix): ?string
    {
        $paddings = config('design.paddings');
        if (!$paddings) {
            return null;
        }

        $paddings = $paddings($context);

        if (!isset($paddings[$id])) {
            return null;
        }

        return str_replace('{{prefix}}', $prefix, $paddings[$id]['classes']);
    }

    /** @return string[]|string[][] */
    public function getRowThemesList(): array
    {
        $rowThemes = config('design.row_themes');

        $rowThemesList = [];

        foreach ($rowThemes as $key => $item) {
            $label = (string)$item['label'];

            if (empty($item['sub_themes'])) {
                $rowThemesList[$key] = $label;
            } else {
                /** @var array<string, string> $rowSubThemeList */
                $rowSubThemeList = [];
                foreach ($item['sub_themes'] as $subKey => $subItem) {
                    $rowSubThemeList["{$key}**{$subKey}"] = "{$label} + {$subItem['label']}";
                }

                $rowSubThemeList[$key] = $label;
                $rowThemesList[$label] = $rowSubThemeList;
            }
        }

        return $rowThemesList;
    }

    /** @return string[] */
    public function getMarginsList(mixed $context = null): array
    {
        $margins = config('design.margins');

        if ($margins instanceof Closure) {
            $margins = $margins($context);
        }

        if (is_array($margins)) {
            return array_map(fn($item) => (string)$item['label'], $margins);
        }

        return [];
    }

    /** @return string[] */
    public function getPaddingsList(mixed $context = null): array
    {
        $paddings = config('design.paddings');

        if ($paddings instanceof Closure) {
            $paddings = $paddings($context);
        }

        if (is_array($paddings)) {
            return array_map(fn($item) => (string)$item['label'], $paddings);
        }

        return [];
    }
}
