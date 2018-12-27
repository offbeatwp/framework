<?php
namespace OffbeatWP\Tools\Acf\Layout;

use OffbeatWP\Tools\Acf\FieldsMapper;

class Fields {

    protected $service;

    public function __construct($service)
    {
        $this->service = $service;

        add_action('acf/init', function () {
            $this->make();
        });
    }

    public function makeComponents() {
        $components = [];

        if(!empty($this->service->components)) foreach ($this->service->components as $name => $component) {
            $componentSettings = $component::settings();

            $form = [];

            $formFields = $component::getForm();

            if (!empty($formFields)) {
                $fieldsMapper = new FieldsMapper($formFields, $componentSettings['slug']);
                $fields = $fieldsMapper->map();

                $componentKey = 'component_' . $name;

                $components[$componentKey] = [
                    'key' => $componentKey,
                    'name' => $name,
                    'label' => $componentSettings['name'],
                    'display' => 'row',
                    'sub_fields' => $fields,
                    'min' => '',
                    'max' => '',
                ];
            }
        }

        return $components;
    }

    public function makeRowFields()
    {
        $rowFields = [
            array(
                'key' => 'field_5c16d2ee4177f',
                'label' => __('Components', 'raow'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_5c16d191e5383',
                'label' => __('Component', 'raow'),
                'name' => 'component',
                'type' => 'flexible_content',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layouts' => $this->makeComponents(),
                'button_label' => __('Add component', 'raow'),
                'min' => '',
                'max' => '',
            ),
        ];

        $rowFields = array_merge($rowFields, $this->makeRowSettings());

        return $rowFields;
    }

    public function makeRowSettings()
    {
        $rowSettings = [
            array(
                'key' => 'field_5c16d30841780',
                'label' => __('Row Settings', 'raow'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'placement' => 'top',
                'endpoint' => 0,
            ),
        ];

        $appearanceFields = [];
        $rowComponent = offbeat('components')->get('row');
        if (method_exists($rowComponent, 'variations')) {
            $variations = collect($rowComponent::variations());
            $variations = $variations->map(function ($item, $key) {
                return $item['label'];
            });

            $appearanceFields[] = [
                'key' => 'field_5c16d32c41789',
                'label' => __('Width', 'raow'),
                'name' => 'width',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => $variations->toArray(),
                'default_value' => array(
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ];
        }

        $rowThemes   = offbeat('design')->getRowThemesList();
        if(is_array($rowThemes)) {
            $appearanceFields[] = [
                'key' => 'field_5c16d32c41786',
                'label' => __('Row theme', 'raow'),
                'name' => 'row_theme',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => $rowThemes,
                'default_value' => array(
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ];
        }


        $rowSettings[] = [
            'key'           => 'field_5c16d30841789',
            'name'          => 'appearance',
            'label'         => __('Appearance', 'raow'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => $appearanceFields
        ];


        $margins    = offbeat('design')->getMarginsList('row');

        $rowSettings[] = [
            'key'           => 'field_5c16d30841781',
            'name'          => 'margins',
            'label'         => __('Margins', 'raow'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => [
                array(
                    'key' => 'field_5c16d32c41782',
                    'label' => __('Margin Top', 'raow'),
                    'name' => 'margin_top',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $margins,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_5c16d32c41783',
                    'label' => __('Margin Bottom', 'raow'),
                    'name' => 'margin_bottom',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $margins,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
            ]
        ];

        $paddings   = offbeat('design')->getPaddingsList('row');

        $rowSettings[] = [
            'key'           => 'field_5c16d30841782',
            'name'          => 'paddings',
            'label'         => __('Paddings', 'raow'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => [
                array(
                    'key' => 'field_5c16d32c41784',
                    'label' => __('Padding top', 'raow'),
                    'name' => 'padding_top',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $paddings,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_5c16d32c41785',
                    'label' => __('Padding bottom', 'raow'),
                    'name' => 'padding_bottom',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $paddings,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
            ]
        ];

        $rowSettings[] = [
            'key'           => 'field_5c16d30841784',
            'name'          => 'misc',
            'label'         => __('Other', 'raow'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => [
                array(
                    'key' => 'field_5c16d32c41787',
                    'label' => __('ID', 'raow'),
                    'name' => 'id',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_5c16d32c41788',
                    'label' => __('Class', 'raow'),
                    'name' => 'css_class',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
            ]
        ];

        return $rowSettings;
    }

    public function make()
    {
        acf_add_local_field_group(array(
            'key' => 'group_layout',
            'title' => 'Layout',
            'fields' => array(
                array(
                    'key' => 'field_5c16c331388e0',
                    'label' => __('Use Layout editor', 'raow'),
                    'name' => 'layout_enabled',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 0,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),


                array(
                    'key' => 'field_5c16d18ae5382',
                    'label' => __('Rows', 'raow'),
                    'name' => 'layout_row',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => [
                        array(
                            array(
                                'field' => 'field_5c16c331388e0',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ],
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'block',
                    'button_label' => __('Add row', 'raow'),
                    'sub_fields' => $this->makeRowFields(),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ));
    }
}