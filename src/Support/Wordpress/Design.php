<?php

namespace OffbeatWP\Support\Wordpress;

use Closure;
use Illuminate\Support\Collection;

final class Design
{
    /**
     * @param string $id
     * @return mixed
     */
    public function getRowThemeClasses(string $id)
    {
        $rowThemes = config('design.row_themes');
        if (!$rowThemes) {
            return null;
        }

        $subId = null;

        if (str_contains($id, '**')) {
            $ids   = explode('**', $id, 2);
            $id    = $ids[0];
            $subId = $ids[1];
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

    /**
     * @param string $id
     * @param string $context
     * @param string $prefix
     * @return string|null
     */
    public function getMarginClasses($id, $context, $prefix)
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

    /**
     * @param string $id
     * @param string $context
     * @param string $prefix
     * @return string|null
     */
    public function getPaddingClasses($id, $context, $prefix)
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

    /** @return mixed[] */
    public function getRowThemesList()
    {
        /** @var Collection<string|int, array{label: string, sub_themes: Collection<string|int, array{label: string}>}>|null $rowThemes */
        $rowThemes = config('design.row_themes');
        if (!is_iterable($rowThemes)) {
            return [];
        }

        $rowThemesList = [];

        foreach ($rowThemes as $key => $item) {
            if (!empty($item['sub_themes'])) {
                $rowThemeList = [$key => $item['label']];

                foreach ($item['sub_themes'] as $subKey => $subItem) {
                    $rowThemeList["{$key}**{$subKey}"] = "{$item['label']} + {$subItem['label']}";
                }

                $rowThemesList[$item['label']] = $rowThemeList;
            } else {
                $rowThemesList[$key] = $item['label'];
            }
        }

        return $rowThemesList;
    }

    /**
     * @param string $context
     * @return mixed[]
     */
    public function getMarginsList($context = null)
    {
        $margins = config('design.margins');

        if ($margins instanceof Closure) {
            $margins = $margins($context);
        }

        if (is_array($margins)) {
            return array_map(fn ($item) => $item['label'], $margins);
        }

        return [];
    }

    /**
     * @param string $context
     * @return mixed[]
     */
    public function getPaddingsList($context = null)
    {
        $paddings = config('design.paddings');

        if ($paddings instanceof Closure) {
            $paddings = $paddings($context);
        }

        if (is_array($paddings)) {
            return array_map(fn ($item) => $item['label'], $paddings);
        }

        return [];
    }
}
