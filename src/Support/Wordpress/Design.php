<?php
namespace OffbeatWP\Support\Wordpress;

class Design
{
    public function getRowThemeClasses($id)
    {
        $rowThemes  = config('design.row_themes');
        if (!$rowThemes) return null;

        $subId      = null;

        if (strpos($id, '**') !== false) {
            $ids   = explode('**', $id);
            $id    = $ids[0];
            $subId = $ids[1];
        }

        if (!isset($rowThemes[$id])) {
            return null;
        }

        $classes = $rowThemes[$id]['classes'];

        if (!is_null($subId) && isset($rowThemes[$id]['sub_themes'][$subId])) {
            $classes .= ' ' . $rowThemes[$id]['sub_themes'][$subId]['classes'];
        }

        return $classes;
    }

    public function getMarginClasses($id, $context, $prefix)
    {
        $margins = config('design.margins');
        if (!$margins) return null;

        $margins = $margins($context);

        if (!isset($margins[$id])) {
            return null;
        }

        return str_replace('{{prefix}}', $prefix, $margins[$id]['classes']);
    }

    public function getPaddingClasses($id, $context, $prefix)
    {
        $paddings = config('design.paddings');
        if (!$paddings) return null;

        $paddings = $paddings($context);

        if (!isset($paddings[$id])) {
            return null;
        }

        return str_replace('{{prefix}}', $prefix, $paddings[$id]['classes']);
    }

    public function getRowThemesList()
    {
        $rowThemes = config('design.row_themes');
        if(!$rowThemes) return [];

        $rowThemesList = [];

        $rowThemes->each(function ($item, $key) use (&$rowThemesList) {
            if (isset($item['sub_themes']) && !empty($item['sub_themes'])) {
                $subThemes       = collect($item['sub_themes']);
                $rowThemeList    = [];
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

    public function getMarginsList($context = null)
    {
        $margins = config('design.margins');
        if(!$margins) return [];

        if ($margins instanceof \Closure) {
            $margins = collect($margins($context));
        }

        return $margins->map(function ($item, $key) {
            return $item['label'];
        })->toArray();
    }

    public function getPaddingsList($context = null)
    {
        $paddings = config('design.paddings');
        if(!$paddings) return [];

        if ($paddings instanceof \Closure) {
            $paddings = collect($paddings($context));
        }

        return $paddings->map(function ($item, $key) {
            return $item['label'];
        })->toArray();
    }
}
