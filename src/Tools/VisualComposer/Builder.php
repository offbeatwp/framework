<?php

namespace OffbeatWP\Tools\VisualComposer;

use \OffbeatWP\Tools\Acf\FieldsMapper as AcfFieldsMapper;

class Builder {

    public function __construct()
    {
    }

    public static function removeDefaultElements () {
        if( ! function_exists('vc_remove_element') ) return null;

        $disabled_elements = [
            'vc_btn',
            'vc_cta',
            'vc_custom_heading',
            'vc_empty_space',
            'vc_flickr',
            'vc_gallery',
            'vc_hoverbox',
            'vc_icon',
            'vc_images_carousel',
            'vc_line_chart',
            'vc_message',
            'vc_pie',
            'vc_posts_slider',
            'vc_progress_bar',
            'vc_round_chart',
//            'vc_separator',
//            'vc_text_separator',
            'vc_toggle',
            'vc_zigzag',
            'vc_heading_element',
            'vc_icon_element',
            'vc_accordion_tab',
            'vc_accordion',
            'vc_button',
            'vc_button2',
            'vc_carousel',
            'vc_cta_button',
            'vc_cta_button2',
            'vc_posts_grid',
            'vc_tab',
            'vc_tabs',
            'vc_tour',
            'vc_basic_grid',
            'vc_masonry_grid',
            'vc_masonry_media_grid',
            'vc_media_grid',
            'vc_facebook',
            'vc_googleplus',
            'vc_pinterest',
            'vc_tweetmeme',
//            'vc_raw_html',
            'vc_raw_js',
            'vc_widget_sidebar',
            'vc_wp_archives',
            'vc_wp_calendar',
            'vc_wp_categories',
            'vc_wp_custommenu',
            'vc_wp_links',
            'vc_wp_meta',
            'vc_wp_pages',
            'vc_wp_posts',
            'vc_wp_recentcomments',
            'vc_wp_rss',
            'vc_wp_search',
            'vc_wp_tagcloud',
            'vc_wp_text',
            'vc_acf',

        ];

        $disabled_elements = apply_filters('vc_disabled_elements', $disabled_elements);

        if ( ! empty($disabled_elements)) foreach ($disabled_elements as $disabled_element) 
        {
            vc_remove_element( $disabled_element );
        }
    }

    public static function registerSectionFieldType()
    {
        vc_add_shortcode_param( 'section', function ($settings, $value) {
            if (!isset($settings['title']) || empty($settings['title'])) return null;

            return '<h3 style="margin-top: 0;">' . $settings['title'] . '</h3>';
        } );
    }

    public static function registerAcfFieldType()
    {
        vc_add_shortcode_param( 'acf', function ($settings, $value) {
            if (isset($_POST['params']) && isset($_POST['params']['content'])) {
                add_filter( "acf/load_value/key=field_{$settings['param_name']}", function ($value, $post_id, $field ) {
                    $content = stripslashes($_POST['params']['content']);
                    $content = preg_replace('/<script>(.*)<\/script>/', '$1', $content);
                    $data = json_decode($content, true);

                    return $data[$field['key']];
                }, 99, 3 );
            }

            ob_start();   
                $fieldsMapper = new AcfFieldsMapper($settings['raw']);
                $fields = $fieldsMapper->map();

                acf_prefix_fields( $fields, 'acf' );
                acf_render_fields( 'vc_field', $fields, 'div', '' );

                echo "<script type=\"text/javascript\">
                    (function($) {            
                        var acfHandler = function () {
                            var values = $(this).closest('.vc_ui-panel').find('[name^=\"acf\"]:not([name*=\"acfcloneindex\"])').serializeJSON();
                            $(this).closest('.vc_ui-panel').find('[name=\"content\"]').val('<script>' + JSON.stringify(values.acf) + '<\/script>');
                        };
                        var acfSelector = '[data-vc-ui-element=\"button-save\"]';
                        
                        $(acfSelector).off( 'click.acfSerialize' );
                        $(acfSelector).on( 'click.acfSerialize', acfHandler );

                        acf.do_action('append', $('div[data-name=\"{$settings['param_name']}\"]') );
                    })(jQuery);
                </script>";

                $acfField = ob_get_contents();

            ob_end_clean();

            return $acfField;
        } );
    }

    public static function registerDynamicDropdown() {
        vc_add_shortcode_param( 'dynamic_dropdown', function ($settings, $value) {
            global $post;

            if ( ! isset($settings['options_callback']) ) return 'No callback set';

            $dropdown_options = $settings['options_callback'];

            $select_options = call_user_func_array($settings['options_callback'], $settings);

            $html .= '<select name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-input wpb-select ' . esc_attr( $settings['param_name'] ) . ' dropdown">';

            if (isset($settings['empty_option']) && $settings['empty_option'] == true) {
                $html .= '<option></option>';
            }

            if (is_array($select_options) && !empty($select_options)) foreach ($select_options as $option_value => $option_label) {
                if ($option_value == '-1') $option_value = '';

                $selected = '';
                if ($option_value == esc_attr( $value )) $selected = ' selected="selected"';
                $html .= '<option value="' . $option_value . '"' . $selected . '>' . $option_label . '</option>';
            }

            $html .= '</select>';

            return $html;
        } );
    }

    public static function registerHiddenFieldType()
    {
        vc_add_shortcode_param( 'hidden', function ($settings, $value) {
           return '<div class="hidden_field_block" style="display: none;">'
                     .'<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-textinput ' .
                     esc_attr( $settings['param_name'] ) . ' ' .
                     esc_attr( $settings['type'] ) . '_field" type="text" value="' . esc_attr( $value ) . '" />' .
                     '</div>'; // This is html markup that will be outputted in content elements edit form
        });
    }

    public function rowThemes()
    {
        $return = [];

        $rowThemes = config('design.row_themes');

        foreach ($rowThemes as $rowThemeKey => $rowTheme) {
            $return[$rowTheme['label']] = $rowTheme['classes'];

            foreach ($rowTheme['children'] as $childKey => $child) {
                $return[$rowTheme['label'] . ' ' . $child['label']] = $rowTheme['classes'] . ' ' . $child['classes'];
            }
        }

        return $return;
    }

    public function margins($prefix, $context)
    {
        $marginsReturn = [];
        $margins = config('design.margins');

        if (is_callable($margins)) {
            $margins = $margins($context);
        }

        if (!is_null($margins)) {
            foreach ($margins as $key => $margin) {
                $margin['classes'] = str_replace('{{prefix}}', $prefix, $margin['classes']);
                $marginsReturn[$margin['label']] = $margin['classes'];
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

        if (!is_null($paddings)) {
            foreach ($paddings as $key => $padding) {
                $padding['classes'] = str_replace('{{prefix}}', $prefix, $padding['classes']);

                $paddingsReturn[$padding['label']] = $padding['classes'];
            }
            return $paddingsReturn;
        }

        return [];
    }

    public function registerSettingsForm()
    {
        $attributes = [
            [
                'type'       => 'dropdown',
                'heading'    => __('Row style', 'raow'),
                'param_name' => 'row_style',
                'group'      => __('Style', 'raow'),
                'value'      => $this->rowThemes(),
            ],
            [
                'type'       => 'dropdown',
                'heading'    => __('Margin top', 'raow'),
                'param_name' => 'margin_top',
                'group'      => __('Style', 'raow'),
                'value'      => $this->margins('mt', 'row'),
            ],
            [
                'type'       => 'dropdown',
                'heading'    => __('Margin bottom', 'raow'),
                'param_name' => 'margin_bottom',
                'group'      => __('Style', 'raow'),
                'value'      => $this->margins('mb', 'row'),
            ],
            [
                'type'       => 'dropdown',
                'heading'    => __('Padding top', 'raow'),
                'param_name' => 'padding_top',
                'group'      => __('Style', 'raow'),
                'value'      => $this->paddings('pt', 'row'),
            ],
            [
                'type'       => 'dropdown',
                'heading'    => __('Padding bottom', 'raow'),
                'param_name' => 'padding_bottom',
                'group'      => __('Style', 'raow'),
                'value'      => $this->paddings('pb', 'row'),
            ],
        ];
        vc_add_params('vc_row', $attributes);
    }

}
