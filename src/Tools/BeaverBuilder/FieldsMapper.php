<?php
namespace OffbeatWP\Tools\BeaverBuilder;

class FieldsMapper {
    public $fields = [];

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function map($fields = null)
    {
        if(is_null($fields))
            $fields = $this->fields;

        $mapping = [];

        if (isset($fields['type'])) {
            $mapping[] = array_merge($mapping, $this->mapField($fields));
        } else {
            foreach ($fields as $entry) {
                if (isset($entry['name'])) {
                    $mapping = array_merge($mapping, $this->mapField($entry));
                } elseif (isset($entry['fields'])) {
                    $mapping = array_merge($mapping, $this->mapSection($entry));
                } else {
                    $mapping = array_merge($mapping, $this->mapTab($entry));
                }
            }
        }

        return $mapping;
        
    }

    public function mapField($field)
    {
        switch ($field['type']) {
            case 'true_false':
                $field['type'] = 'select';
                $field['options'] = [
                    '0' => __('No', 'raow'),
                    '1' => __('Yes', 'raow'),
                ];

                if (isset($field['default']) && $field['default'] === true) {
                    $field['default'] = '1';
                }

                break;

            case 'repeater':
                $formId = 'form_' . $field['name'];

                $this->registerForm($formId, $field['label'], $field['fields']);

                $field['type']          = 'form';
                $field['multiple']      = true;
                $field['form']          = $formId;
                $field['preview_text']  = $field['fields'][0]['name'];

                break;

            case 'image':
                $field['type']          = 'photo';

                break;

            case 'post_type':
                $field['type']          = 'post-type';

                break;

            case 'posts':
            case 'terms':

                if ($field['type'] == 'posts') {
                    $field['action']        = 'fl_as_posts';
                    $field['data']          = $field['postTypes'];
                } elseif ($field['type'] == 'terms') {
                    $field['action']        = 'fl_as_terms';
                    $field['data']          = $field['taxonomies'];
                }

                $field['type']          = 'suggest';

                $limit = 1;
                if (isset($field['multiple']) && $field['multiple']) {
                    unset($field['multiple']);

                    $limit = null;

                    if (isset($field['limit']) && isset($field['limit'])) {
                        $limit = $field['limit'];
                    }
                }

                $field['limit'] = $limit;

                break;
        }

        return [$field['name'] => $field];
    }

    public function mapSection($section)
    {
        $sectionMapping = [
            'fields' => $this->map($section['fields'])
        ];

        if (isset($section['title'])) {
            $sectionMapping['title'] = $section['title'];
        }

        return [
            'section-' . $section['id'] => $sectionMapping
        ];
    }

    public function mapTab($tab)
    {
        $subMapping = [];

        if (isset($tab['sections'])) {
            $subMapping = [
                'title'     => $tab['title'],
                'sections'  => $this->map($tab['sections'])
            ];
        }

        if (isset($tab['fields'])) {
            $subMapping = [
                'title'     => $tab['title'],
                'fields'  => $this->map($tab['fields'])
            ];
        }

        return [
            'tab-' . $tab['id'] => $subMapping
        ];
    }

    public function registerForm($formId, $label, $fields)
    {
        $fieldsMapper = new FieldsMapper([[
            'id'  => 'general',
            'title'  => __('General', 'raow'),
            'sections' => [[
                'id' => 'general',
                'title'  => __('General', 'raow'),
                'fields' => $fields
            ]]
        ]]);

        \FLBuilder::register_settings_form($formId, [
            'title' => __($label, 'raow'),
            'tabs'  => $fieldsMapper->map()
        ]);

    }
}