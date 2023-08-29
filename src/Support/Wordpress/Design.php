<?php
namespace OffbeatWP\Support\Wordpress;

use Closure;

class Design
{
    /**
     * @param string $id
     * @return mixed
     */
    public function getRowThemeClasses(string $id)
    {
        $rowThemes  = config('design.row_themes');
        if (!$rowThemes) {
            return null;
        }

        $subId = null;

        if (strpos($id, '**') !== false) {
            $ids   = explode('**', $id);
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
        $rowThemes = config('design.row_themes');
        if(!$rowThemes) {
            return [];
        }

        $rowThemesList = [];

        $rowThemes->each(function ($item, $key) use (&$rowThemesList) {
            if (!empty($item['sub_themes'])) {
                $subThemes       = collect($item['sub_themes']);
                $rowSubThemeList = [];

                $subThemes->each(function ($subItem, $subKey) use ($item, $key, &$rowSubThemeList) {
                    $rowSubThemeList["{$key}**{$subKey}"] = "{$item['label']} + {$subItem['label']}";
                });

                $rowThemeList = array_merge(
                    [$key => $item['label']],
                    $rowSubThemeList
                );

                $rowThemesList[$item['label']] = $rowThemeList;
            } else {
                $rowThemesList[$key] = $item['label'];
            }
        });

        return $rowThemesList;
    }

    /**
     * @param string $context
     * @return mixed[]
     */
    public function getMarginsList($context = null)
    {
        $margins = config('design.margins');
        if(!$margins) {
            return [];
        }

        if ($margins instanceof Closure) {
            $margins = collect($margins($context));
        }

        return $margins->map(function ($item) {
            return $item['label'];
        })->toArray();
    }

    /**
     * @param string $context
     * @return mixed[]
     */
    public function getPaddingsList($context = null)
    {
        $paddings = config('design.paddings');
        if(!$paddings) {
            return [];
        }

        if ($paddings instanceof Closure) {
            $paddings = collect($paddings($context));
        }

        return $paddings->map(function ($item) {
            return $item['label'];
        })->toArray();
    }
}
