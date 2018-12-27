<?php

namespace OffbeatWP\Tools\VisualComposer;

use OffbeatWP\Components\ComponentInterfaceTrait;
use OffbeatWP\Fields\Helper as FieldsHelper;
use \OffbeatWP\Tools\Acf\FieldsMapper as AcfFieldsMapper;

class VisualComposerElement
{
    use ComponentInterfaceTrait;

    public $settings;
    public $componentClass;

    public function __construct($settings = [])
    {
        $this->settings       = $settings;
        $this->componentClass = $settings['componentClass'];

        add_shortcode($settings['shortcode'], [$this, 'renderShortcode']);

        add_action('vc_before_init', [$this, 'vcMap']);
    }

    public function vcMap()
    {
        $settings = $this->settings;

        $vc_element_config = [
            "name"             => __($settings['name'], "raow"),
            "base"             => $settings['shortcode'],
            "class"            => "",
            "category"         => __($settings['category'], "raow"),
            "admin_enqueue_js" => get_template_directory_uri() . '/vendor/raow/core-theme/assets/js/jquery.serializejson.min.js',
            // "custom_markup" => '',
        ];

        $fieldsMapper                = new FieldsMapper($this->getForm());
        $vc_element_config['params'] = $fieldsMapper->map();

        vc_map($vc_element_config);
    }

    public function renderShortcode($atts, $content)
    {
        $form = $this->getForm();

        $defaultAtts = FieldsHelper::getDefaults($form);
        $acfAtts     = $this->mapAcfFields($form, $content);

        $atts = shortcode_atts(
            $defaultAtts,
            array_merge($atts, $acfAtts),
            $this->settings['shortcode']
        );

        if (empty($acfAtts) && !empty(trim($content))) {
            $atts['content'] = $content;
        }

        return $this->render($atts);
    }

    public function getForm()
    {
        $form = $this->componentClass::getForm();

        if (empty($form)) {
            $form = [];
        }

        return $form;
    }

    public function mapAcfFields($fields, $content)
    {
        $fieldsMapper = new FieldsMapper($fields);
        $fields       = $fieldsMapper->map();

        $acfFields = [];
        $acfAtts   = [];

        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($field['type'] == 'acf') {
                    $acfFields[] = $field['raw'];
                }
            }
        }

        if (!empty($acfFields)) {
            $content = preg_replace('/<script>(.*)<\/script>/', '$1', $content);

            $rawAcfAtts = json_decode($content, true);

            if (empty($rawAcfAtts)) {
                return $acfAtts;
            }

            foreach ($acfFields as $acfField) {
                $fieldKey = 'field_' . $acfField['name'];

                if (isset($rawAcfAtts[$fieldKey])) {
                    $acfAtts[$acfField['name']] = $rawAcfAtts[$fieldKey];
                }
            }

            $acfAtts = $this->formatAcfFields($acfAtts, $acfFields);
        }

        return $acfAtts;
    }

    public function formatAcfFields($atts, $fields)
    {
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!isset($atts[$field['name']])) {
                    continue;
                }

                $value = $atts[$field['name']];

                $fieldsMapper = new AcfFieldsMapper($field);
                $acfField     = $fieldsMapper->map();

                if (isset($acfField[0]['sub_fields'])) {
                    $subfields = $acfField[0]['sub_fields'];
//
                    $subfieldsMapped = [];

                    foreach ($subfields as $subfield) {
                        $subfieldsMapped[$subfield['key']] = $subfield;
                    }

                    $items = [];

                    if (!empty($value)) {
                        foreach ($value as $item) {
                            $item2 = [];

                            if (!empty($item)) {
                                foreach ($item as $subfieldName => $subfield) {
                                    $name = $subfieldsMapped[$subfieldName]['name'];

                                    $item2[$name] = acf_format_value($subfield, uniqid(), $subfieldsMapped[$subfieldName]);

                                }
                            }

                            $items[] = $item2;
                        }
                    }

                    $atts[$field['name']] = $items;

                } else {
                    $atts[$field['name']] = acf_format_value($value, uniqid(), $acfField[0]);
                }
            }
        }

        return $atts;

    }
}
