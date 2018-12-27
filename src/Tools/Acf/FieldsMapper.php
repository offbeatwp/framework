<?php
namespace OffbeatWP\Tools\Acf;

class FieldsMapper {
    public $fields = [];
    public $mappedFields = [];
    public $keyPrefix = '';

    public function __construct($fields, $keyPrefix = '')
    {
        $this->fields = $fields;
        $this->keyPrefix = $keyPrefix;
    }

    public function map($fields = null, $global = true)
    {
        $root = false;
        $mapping = null;        

        if(is_null($fields)) {
            $root = true;
            $fields = $this->fields;
        }

        if (isset($fields['type'])) {
            $mapping[] = $this->mapField($fields, $global);
        } else {
            foreach ($fields as $entry) {
                if (isset($entry['name'])) {
                    $mapping[] = $this->mapField($entry, $global);
                } elseif (isset($entry['fields'])) {
                    $mapping[] = $this->mapSection($entry);
                } else {
                    $mapping[] = $this->mapTab($entry);
                }
            }
        }

        if ($root) return $this->mappedFields;

        return $mapping;
        
    }

    public function mapField($field, $global)
    {
        $mappedField = [
            'key'           => $this->getKey('field', $field['name']),
            'label'         => $field['label'],
            'name'          => $field['name'],
            'type'          => $this->mapFieldType($field['type']),
            'required'      => 0,
        ];

        if (isset($field['default'])) 
            $mappedField['default_value'] = $field['default'];

        if (isset($field['placeholder'])) 
            $mappedField['placeholder'] = $field['placeholder'];

        if (isset($field['multiple'])) 
            $mappedField['multiple'] = $field['multiple'];

        switch ($mappedField['type']) {
            case 'repeater':
                $mappedField['layout'] = 'block';
                $mappedField['sub_fields'] = $this->map($field['fields'], false);

                if (isset($field['collapsed'])) {
                    $mappedField['collapsed'] = $this->getKey('field', $field['collapsed']);
                }
                break;
            case 'select':
            case 'checkbox':
                $mappedField['choices'] = $field['options'];
                $mappedField['return_format'] = 'value';
                break;
            case 'post_object':
                $mappedField['post_type'] = [];

                if (isset($field['postTypes']) && !empty($field['postTypes'])) {
                    $mappedField['post_type'] = array_merge($mappedField['post_type'], $field['postTypes']);
                }

                if (isset($field['data']) && !empty($field['data'])) {
                    $mappedField['post_type'][] = $field['data'];
                }

                $mappedField['return_format'] = 'object';
                break;
            case 'taxonomy':
                $mappedField['taxonomy'] = $field['taxonomies'];
                $mappedField['return_format'] = 'id';
                break;
            case 'image':
                $mappedField['return_format'] = 'id';
                break;
        }

         if ($global)
            $this->mappedFields[] = $mappedField;

        return $mappedField;
    }

    public function mapFieldType($fieldType, $global = true)
    {  
        switch ($fieldType) {
            case 'editor':
                $fieldType = 'wysiwyg';
                break;
            case 'posts':
                $fieldType = 'post_object';
                break;
            case 'terms':
                $fieldType = 'taxonomy';
                break;
        }
        return $fieldType;
    }

    public function mapSection($section, $global = true)
    {
        $mappedSection = [
           'key'           => $this->getKey('section', $section['id']),
           'name'          => $section['id'],
           'label'         => $section['title'],
           'type'          => 'group',
           'layout'        => 'block',
        ];

        if (isset($section['fields'])) {
           $mappedSection['sub_fields'] = $this->map($section['fields'], false);
        }

        if ($global)
           $this->mappedFields[] = $mappedSection;

        return $mappedSection;
        // return [];
    }

    public function mapTab($tab, $global = true)
    {
        $mappedTab = [
            'key'   => $this->getKey('tab', $tab['id']),
            'label' => $tab['title'],
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
            'endpoint' => 0,
        ];

        if ($global)
            $this->mappedFields[] = $mappedTab;

        if (isset($tab['sections'])) {
            $this->map($tab['sections']);
        }

        if (isset($tab['fields'])) {
            $this->map($tab['fields']);
        }

        return $mappedTab;
    }

    public function getKey($type, $key) {
        $suffix = !empty($this->keyPrefix) ? '_' . $this->keyPrefix : '';
        return $type . $suffix . '_' . $key;
    }
}