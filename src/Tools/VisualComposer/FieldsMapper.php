<?php
namespace OffbeatWP\Tools\VisualComposer;

class FieldsMapper {
    public $fields = [];
    public $firstGroup = null;
    public $hasAcfField = false;
    public $mappedEntries = [];

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function map()
    {
        $entries = $this->fields;
        $mappedFields = '';

        if (isset($entries['type'])) {

            $this->mapField($entries);

        } elseif (isset($entries[0]['type'])) {

            $this->mapEntries($entries, 'mapField');

        } elseif (isset($entries[0]['fields'])) {

            $this->mapEntries($entries, 'mapSection');

        } else {
            $this->mapEntries($entries, 'mapTab');
        }

        if ($this->hasAcfField) {
            $acfHiddenContent = [[
              "type"        => 'hidden',
              "param_name"  => 'content',
              "group"       => $this->firstGroup,
            ]];

            $this->mappedEntries = array_merge($this->mappedEntries, $acfHiddenContent);
        }

        return $this->mappedEntries;
    }

    public function mapEntries($entries, $method, $group = null)
    {
        if (!empty($entries)) foreach ($entries as $entry) {
            $this->$method($entry, $group);
        }
    }

    public function mapField($field, $group = null)
    {
        $mappedField = [
          "type"        => $this->mapFieldType($field['type'], $field['name']),
          "holder"      => "div",
          "class"       => "",
          "heading"     => __( $field['label'], "raow" ),
          "param_name"  => $field['name'],
          "value"       => __( (isset ($field['default']) ? $field['default'] : ''), "raow" ),
          "group"       => $group,
        ];

        if (isset($field['description'])) 
            $mappedField['description'] = __( $field['description'], "raow" );

        switch ($mappedField['type']) {
            case 'dropdown':
                if (is_string($field['options'])) {
                    $mappedField['type'] =              'dynamic_dropdown';
                    $mappedField['options_callback'] =  $field['options'];
                } else {
                    $mappedField['value'] = array_flip($field['options']);
                }
                break;
            case 'acf':
                $this->hasAcfField = true;

                if(isset($field['fields'])) {
                    $mappedField['fields'] = $field['fields'];
                }
                $mappedField['raw'] = $field;
                unset($mappedField['heading']);
                break;
        }

        $this->mappedEntries[] = $mappedField;
    }

    public function mapFieldType($type, $name)
    {
        switch ($type) {
            case 'text':
                $type = 'textfield';
                break;
            case 'select':
                $type = 'dropdown';
                break;
            case 'image':
                $type = 'attach_image';
                break;
            case 'true_false':
                $type = 'checkbox';
                break;
            case 'repeater':
            case 'posts':
            case 'terms':
                $type = 'acf';
                break;
            case 'post_type':
                $type = 'posttypes';
            case 'editor':
                if($name == 'content') {
                    $type = 'textarea_html';
                } else {
                    $type = 'acf';
                }
                break;
        }

        return $type;
    }

    public function mapSection($section, $group = null)
    {
        $this->mappedEntries[] = [
            "type"          => "section",
            "holder"        => "div",
            "class"         => "",
            "param_name"    => uniqid(),
            'group'         => $group,
        ];

        if (isset($section['title'])) {
            $mappedSection['param_name'] =  'heading';
            $mappedSection['title'] =       __($section['title'], "raow");
        }

        if (isset($section['fields'])) {
            $this->mapEntries($section['fields'], 'mapField', $group);
        }
    }

    public function mapTab($tab, $group = null)
    {
        if (is_null($this->firstGroup)) {
            $this->firstGroup = $tab['title'];
        }

        if (isset($tab['sections'])) {
            $this->mapEntries($tab['sections'], 'mapSection', $tab['title']);
        }

        if (isset($tab['fields'])) {
            $this->mapEntries($tab['fields'], 'mapField', $tab['title']);
        }
    }
}