<?php

namespace OffbeatWP\Tools\Acf;

use OffbeatWP\SiteSettings\AbstractSiteSettings;

class SiteSettings extends AbstractSiteSettings {

    const ID = 'site-settings';

    public function register()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page(array(
                'page_title' => 'Site Settings',
                'menu_title' => 'Site Settings',
                'menu_slug'  => self::ID,
                'capability' => 'manage_options',
                'redirect'   => true
            ));
        }

        add_filter('acf/format_value/type=relationship', [$this, 'convertPostObject'], 99, 3);

        add_action('acf/init', [$this, 'registerAcfSiteSettings']);
    }

    public function convertPostObject($value, $post_id, $field)
    {
        if($field['return_format'] != 'object' || empty($value)) return $value;

        foreach ($value as &$postObject) {
            $postObject = new \OffbeatWP\Content\Post($postObject);
        }

        return $value;
    }

    public function addSection($class)
    {
        if (!is_admin() || (wp_doing_ajax() && !preg_match('/^acf/', $_REQUEST['action'])) || !class_exists($class) || !function_exists('acf_add_options_sub_page')) {
            return null;
        }

        $sectionConfig = container()->make($class);
        
        $priority = 10;
        if (defined("{$class}::PRIORITY")) $priority = $class::PRIORITY;

        add_action('acf_site_settings', function () use ($sectionConfig, $class) {            
            $title = $sectionConfig->title();
            $subMenuSlug = self::ID . '-' . $sectionConfig::ID;

            acf_add_options_sub_page([
                'page_title'    => $title,
                'menu_title'    => $title,
                'parent_slug'   => self::ID,
                'menu_slug'     => $subMenuSlug,
            ]);

            if (method_exists($sectionConfig, 'fields')) {
                $fields = $sectionConfig->fields($subMenuSlug);

                if (is_array($fields)) {
                    $fieldsMapper = new FieldsMapper($fields);
                    $mappedFields = $fieldsMapper->map();

                    acf_add_local_field_group(array(
                        'key'                   => 'group_' . str_replace(' ', '_', strtolower($title)),
                        'title'                 => $title,
                        'fields'                => $mappedFields,
                        'location'              => [
                            [
                                [
                                    'param'    => 'options_page',
                                    'operator' => '==',
                                    'value'    => $subMenuSlug,
                                ],
                            ],
                        ],
                        'menu_order'            => 0,
                        'position'              => 'normal',
                        'style'                 => 'seamless',
                        'label_placement'       => 'top',
                        'instruction_placement' => 'label',
                        'active'                => 1,
                    ));
                }
            }

        }, $priority);

    }

    public function get($key)
    {
        return get_field($key, 'option');
    }

    public function update($key, $value)
    {
        return update_field($key, $value, 'option');
    }

    public function registerAcfSiteSettings() {
        do_action('acf_site_settings');
    }
}