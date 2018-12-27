<?php

namespace OffbeatWP\Tools\BeaverBuilder;

class Builder {
    public function registerSettingsForm($form, $id)
    {

        if($id == 'rich-text') {
            $form['general']['sections']['general']['fields']['text_columns'] = [
                'type' => 'select',
                'label' => __('Columns', 'raow'),
                'default' => 1,
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4'
                ]
            ];

            $form['general']['sections']['general']['fields']['text_style'] = [
                'type' => 'select',
                'label' => __('Text style', 'raow'),
                'default' => '',
                'options' => [
                    '' => __('Normal', 'raow'),
                    'lead' => __('Lead', 'raow'),
                ]
            ];
        }

        if ($id == 'module_advanced') {
            $form = [
                'title'    => __('Advanced', 'fl-builder'),
                'sections' => [
                    'margins'    => [
                        'title'  => __('Margins', 'raow'),
                        'fields' => [
                            'margin_top'    => [
                                'type'    => 'select',
                                'label'   => __('Margin Top', 'raow'),
                                'default' => 'default',
                                'options' => $this->margins('mt', 'component'),
                            ],
                            'margin_bottom' => [
                                'type'    => 'select',
                                'label'   => __('Margin Bottom', 'raow'),
                                'default' => 'default',
                                'options' => $this->margins('mb', 'component'),
                            ],
                        ],
                    ],
                    'responsive' => [
                        'title'  => __('Responsive Utilities', 'raow'),
                        'fields' => [
                            'responsive_display_classes' => [
                                'type'         => 'select',
                                'label'        => __('Display', 'raow'),
                                'default'      => '',
                                'options'      => $this->responsiveDisplay(),
                                'multi-select' => true,
                            ],
                        ],
                    ],
                    'other'      => [
                        'title'  => __('Other', 'raow'),
                        'fields' => [
                            'id'    => [
                                'type'  => 'text',
                                'label' => __('CSS ID', 'raow'),
                            ],
                            'class' => [
                                'type'  => 'text',
                                'label' => __('CSS Class', 'raow'),
                            ],
                        ],
                    ],
                ]
            ];
        }

        if ($id == 'row') {
            unset($form['tabs']['style']['sections']['colors']);
            unset($form['tabs']['style']['sections']['border']);
            unset($form['tabs']['style']['sections']['general']['fields']['max_content_width']);

            $form['tabs']['style']['sections']['general']['fields']['width']['options'] = [
                'fixed'     => __('Fixed', 'raow'),
                'full'      => __('Full width', 'raow'),
                'narrow'    => __('Narrow', 'raow'),
                'narrowest' => __('Narrowest', 'raow'),
            ];

            $form['tabs']['style']['sections']['general']['fields']['content_width']['options'] = [
                'fixed'     => __('Fixed', 'raow'),
                'full'      => __('Full width', 'raow'),
                'narrow'    => __('Narrow', 'raow'),
                'narrowest' => __('Narrowest', 'raow'),
            ];

            $rowThemes = $this->rowThemes();

            $rowThemeFields = [
                'fields' => [
                    'row_theme' => [
                        'type'    => 'select',
                        'label'   => __('Theme', 'raow'),
                        'default' => 'default',
                        'options' => $rowThemes['options'],
                        'toggle'  => $rowThemes['toggle'],
                    ],
                ],
            ];

            foreach ($this->rowThemeChildFields() as $rowThemeKey => $rowTheme) {
                $rowThemeFields['fields'][$rowThemeKey] = $rowTheme;
            }

            $form['tabs']['style']['sections']['style'] = [
                'title'  => __('Style', 'raow'),
                'fields' => $rowThemeFields['fields'],
            ];
        }

        if (in_array($id, ['row'])) {
            $form['tabs']['advanced'] = [];

            $form['tabs']['advanced'] = [
                'title'    => __('Advanced', 'fl-builder'),
                'sections' => [],
            ];

            $form['tabs']['advanced']['sections']['margins'] = [
                'title'  => __('Margins', 'raow'),
                'fields' => [
                    'margin_top'    => [
                        'type'    => 'select',
                        'label'   => __('Margin Top', 'raow'),
                        'default' => 'default',
                        'options' => $this->margins('mt', 'row'),
                    ],
                    'margin_bottom' => [
                        'type'    => 'select',
                        'label'   => __('Margin Bottom', 'raow'),
                        'default' => 'default',
                        'options' => $this->margins('mb', 'row'),
                    ],
                ]
            ];

            $form['tabs']['advanced']['sections']['padding'] = [
                'title'  => __('Padding', 'raow'),
                'fields' => [
                    'padding_top'    => [
                        'type'    => 'select',
                        'label'   => __('Padding Top', 'raow'),
                        'default' => 'default',
                        'options' => $this->paddings('pt', 'row'),
                    ],
                    'padding_bottom' => [
                        'type'    => 'select',
                        'label'   => __('Padding Bottom', 'raow'),
                        'default' => 'default',
                        'options' => $this->paddings('pb', 'row'),
                    ],
                ]
            ];

            if ($id == 'row') {
                $form['tabs']['advanced']['sections']['responsive'] = [
                    'title'  => __('Responsive Utilities', 'raow'),
                    'fields' => [
                        'responsive_display_classes' => [
                            'type'         => 'select',
                            'label'        => __('Display', 'raow'),
                            'default'      => '',
                            'options'      => $this->responsiveDisplay(),
                            'multi-select' => true,
                        ],
                    ]
                ];
            }

            $form['tabs']['advanced']['sections']['other'] = [
                'title'  => __('Other', 'raow'),
                'fields' => [
                    'id'    => [
                        'type'  => 'text',
                        'label' => __('CSS ID', 'raow'),
                    ],
                    'class' => [
                        'type'  => 'text',
                        'label' => __('CSS Class', 'raow'),
                    ],
                ]
            ];

        }

        return $form;
    }

    public function margins($prefix, $context)
    {
        $marginsReturn = [];
        $margins = config('design.margins');

        if (is_callable($margins)) {
            $margins = $margins($context);
        }

        if ( ! is_null($margins) )
        {
            foreach ($margins as $key => $margin) {
                $key = str_replace('{{prefix}}', $prefix, $key);
                $margin['classes'] = str_replace('{{prefix}}', $prefix, $margin['classes']);
                $marginsReturn[$key] = $margin;
            }

            return $marginsReturn;
        }

        return [];
    }

    public function paddings($prefix, $context)
    {
        $paddingsReturn = [];
        $paddings = config('design.paddings');

        if (is_callable($paddings)) {
            $paddings = $paddings($context);
        }

        if ( ! is_null($paddings) )
        {
            foreach ($paddings as $key => $padding) {
                $key = str_replace('{{prefix}}', $prefix, $key);
                $padding['classes'] = str_replace('{{prefix}}', $prefix, $padding['classes']);

                $paddingsReturn[$key] = $padding;
            }
            return $paddingsReturn;
        }

        return [];
    }

    public function responsiveDisplay()
    {
        return [
            ''           => [
                'label'     => __('Default', 'raow'),
                'classes' => '',
            ],
            'd-none'     => [
                'label'     => __('Hide xs', 'raow'),
                'classes' => 'd-none',
            ],
            'd-sm-none'  => [
                'label'     => __('Hide sm', 'raow'),
                'classes' => 'd-sm-none',
            ],
            'd-md-none'  => [
                'label'     => __('Hide md', 'raow'),
                'classes' => 'd-md-none',
            ],
            'd-lg-none'  => [
                'label'     => __('Hide lg', 'raow'),
                'classes' => 'd-lg-none',
            ],
            'd-xl-none'  => [
                'label'     => __('Hide xl', 'raow'),
                'classes' => 'd-xl-none',
            ],
            'd-block'    => [
                'label'     => __('Display xs', 'raow'),
                'classes' => 'd-block',
            ],
            'd-sm-block' => [
                'label'     => __('Display sm', 'raow'),
                'classes' => 'd-sm-block',
            ],
            'd-md-block' => [
                'label'     => __('Display md', 'raow'),
                'classes' => 'd-md-block',
            ],
            'd-lg-block' => [
                'label'     => __('Display lg', 'raow'),
                'classes' => 'd-lg-block',
            ],
            'd-xl-block' => [
                'label'     => __('Display xl', 'raow'),
                'classes' => 'd-xl-block',
            ],
        ];
    }

    public function rowThemes()
    {
        $return = [
            'options'   => [],
            'toggle'    => [],
        ];

        $rowThemes = config('design.row_themes');

        foreach ($rowThemes as $rowThemeKey => $rowTheme) {
            $return['options'][$rowThemeKey] = $rowTheme;

            if (isset($rowTheme['children']) && !empty($rowTheme['children'])) {
                foreach ($rowTheme['children'] as $childKey => $child) {
                    $return['toggle'][$rowThemeKey]['fields'][] = $rowThemeKey . '_children';
                }
            }

            unset($return['options'][$rowThemeKey]['children']);
        }

        return $return;
    }

    public function rowThemeChildFields()
    {
        $return = [];

        $rowThemes = config('design.row_themes');

        foreach ($rowThemes as $rowThemeKey => $rowTheme) {

            if (isset($rowTheme['children'])) {
                $return[$rowThemeKey . '_children'] = [
                    'type'    => 'select',
                    'label'   => __('Child theme', 'raow'),
                    'default' => 'default',
                    'options' => $this->rowThemeChildren($rowThemeKey),
                ];
            }
        }

        return $return;
    }

    public function rowThemeChildren($theme)
    {
        $return = [];

        $rowThemes = config('design.row_themes');

        if (isset($rowThemes[$theme])) {

            $return['default'] = [
                'label'     => __('Default', 'raow'),
                'classes'   => '',
            ];

            foreach ($rowThemes[$theme]['children'] as $childKey => $child) {
                $return[$childKey] = $child;
            }
        }

        return $return;
    }

    public function deregisterModules($enabled, $instance)
    {
        $disabled = [
            'audio',
            'button',
            'photo',
            'heading',
            'separator',
            'video',
            'accordion',
            'cta',
            'callout',
            'contact-form',
            'content-slider',
            'countdown',
            'gallery',
            'icon',
            'icon-group',
            'menu',
            'numbers',
            'post-grid',
            'post-carousel',
            'post-slider',
            'pricing-table',
            'sidebar',
            'slideshow',
            'social-buttons',
            'subscribe-form',
            'testimonials',
        ];

        if (in_array($instance->slug, $disabled)) {
            return false;
        }

        return $enabled;
    }

    public function addCustomClasses($class, $row)
    {
        $context = 'row';
        if ($row->type === 'module') $context = 'component';

        // Margin Top
        if (isset($row->settings->margin_top) && $row->settings->margin_top != '') {
            $marginTop = $this->margins('mt', $context);
            $selected = 'default';

            if (isset($marginTop[$row->settings->margin_top])) $selected = $row->settings->margin_top;

            $class .= ' ' . $marginTop[$selected]['classes'];
        }

        // Margin Bottom
        if (isset($row->settings->margin_bottom) && $row->settings->margin_bottom != '') {
            $marginBottom = $this->margins('mb', $context);
            $selected = 'default';

            if (isset($marginBottom[$row->settings->margin_bottom])) $selected = $row->settings->margin_bottom;

            $class .= ' ' . $marginBottom[$selected]['classes'];
        }

        // Padding Top
        if (isset($row->settings->padding_top) && $row->settings->padding_top != '') {
            $paddingTop = $this->paddings('pt', $context);
            $selected = 'default';

            if (isset($paddingTop[$row->settings->padding_top])) $selected = $row->settings->padding_top;

            $class .= ' ' . $paddingTop[$selected]['classes'];
        }

        // Padding Bottom
        if (isset($row->settings->padding_bottom) && $row->settings->padding_bottom != '') {
            $paddingBottom = $this->paddings('pb', $context);
            $selected = 'default';

            if (isset($paddingBottom[$row->settings->padding_bottom])) $selected = $row->settings->padding_bottom;

            $class .= ' ' . $paddingBottom[$selected]['classes'];
        }

        // Themes
        if (isset($row->settings->row_theme) && $row->settings->row_theme != '') {
            $rowThemes = $this->rowThemes();

            if (isset($rowThemes['options'][$row->settings->row_theme])) {
                $class .= ' ' . $rowThemes['options'][$row->settings->row_theme]['classes'];
            }

            if (isset($row->settings->{$row->settings->row_theme . '_children'}) && $row->settings->{$row->settings->row_theme . '_children'} != '') {

                if (isset($this->rowThemeChildren($row->settings->row_theme)[$row->settings->{$row->settings->row_theme . '_children'}])) {
                    $class .= ' ' . $this->rowThemeChildren($row->settings->row_theme)[$row->settings->{$row->settings->row_theme . '_children'}]['classes'];
                }
            }
        }

        // Responsive Display
        if (isset($row->settings->responsive_display_classes) && $row->settings->responsive_display_classes != '') {
            foreach ($row->settings->responsive_display_classes as $responsive_display) {
                $responsiveDisplay = $this->responsiveDisplay();
                if (isset($responsiveDisplay[$responsive_display])) {
                    $class .= ' ' . $responsiveDisplay[$responsive_display]['classes'];
                }
            }
        }

        // Text Columns
        if (isset($row->settings->text_columns) && $row->settings->text_columns != '') {
            $classes = '';

            switch ($row->settings->text_columns) {
                case '2':
                    $classes = 'text-columns-md-2';
                    break;
                case '3':
                    $classes = 'text-columns-md-3';
                    break;
                case '4':
                    $classes = 'text-columns-md-2 text-columns-lg-4';
                    break;
            }

            $class .= ' ' . $classes;
        }

        // Text Style
        if (isset($row->settings->text_style) && $row->settings->text_style != '') {
            $classes = '';

            switch ($row->settings->text_style) {
                case 'lead':
                    $classes = 'lead';
                    break;
            }

            $class .= ' ' . $classes;
        }

        return $class;
    }
}