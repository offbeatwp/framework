<?php

namespace OffbeatWP\Tools\GravityForms;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Contracts\View;

class Service extends AbstractService
{
    public function register(View $view)
    {
        add_filter('gform_get_form_filter', [$this, 'bootstrapClasses'], 10, 2);
        add_filter('gform_field_container', [$this, 'bootstrapContainer'], 10, 6);
        add_filter('gform_field_content', [$this, 'fieldBootstrapClasses'], 10, 5);

        add_filter('gform_submit_button', [$this, 'buttonClassFrontend'], 10, 2);
        add_filter('gform_submit_button', [$this, 'inputToButton'], 10, 2);
        add_filter('gform_form_tag', [$this, 'formActionOnAjax'], 10, 2);

        add_filter('gform_cdata_open', [$this, 'wrapJqueryScriptStart']);
        add_filter('gform_cdata_close', [$this, 'wrapJqueryScriptEnd']);

        if (!is_admin()) {
            add_filter('gform_init_scripts_footer', '__return_true');
        } else {
            add_filter('gform_form_settings', __NAMESPACE__ . '\Admin::buttonClass', 10, 2);
            add_filter('gform_pre_form_settings_save', __NAMESPACE__ . '\Admin::buttonClassProcess', 10, 1);
            add_filter('gform_enable_field_label_visibility_settings', '__return_true');

            add_action('gform_field_appearance_settings', [$this, 'customFields']);
            add_action('gform_editor_js', [$this, 'customFieldSizes']);
            add_filter('gform_tooltips', [$this, 'customFieldTooltips']);
        }

        $this->app->container->get('components')->register('gravityform', Components\GravityForm::class);

        $view->registerGlobal('gravityforms', new Helpers\ViewHelpers());
    }

    public static function bootstrapContainer($field_container, $field, $form, $css_class, $style, $field_content)
    {
        $replacement = 'class=$1$2 form-group';

        if (strpos($field['cssClass'], 'col-') == false) {
            if ($field['colXs'] == '' || !$field['colXs']) {
                $field['colXs'] = '12';
            }

            if ($field['colXs']) {
                $replacement .= ' col-' . $field['colXs'];
            }

            if ($field['colMd']) {
                $replacement .= ' col-md-' . $field['colMd'];
            }

            if ($field['colLg']) {
                $replacement .= ' col-lg-' . $field['colLg'];
            }
        }

        $replacement .= '$3';

        $field_container = preg_replace('/class=(\'|")([^\'"]+)(\'|")/', $replacement, $field_container);

        return $field_container;
    }

    public function bootstrapClasses($formHtml)
    {
        if (preg_match("/class='[^']*gform_validation_error[^']*'/", $formHtml)) {
            preg_match_all("/class='(gfield [^']+)'/", $formHtml, $gFields);

            if (!empty($gFields[0])) {
                foreach ($gFields[0] as $gFieldIndex => $gField) {
                    $class = " is-valid";

                    if (strpos($gFields[1][$gFieldIndex], 'gfield_error') !== false) {
                        $class = ' is-invalid';
                    }

                    $formHtml = str_replace($gField, "class='" . $gFields[1][$gFieldIndex] . $class . "'", $formHtml);
                }
            }

        }

        return $formHtml;
    }

    public function fieldBootstrapClasses($fieldContent, $field, $value, $unknown, $formId)
    {
        if (strpos($fieldContent, '<select') !== false) {
            preg_match_all("/<select[^>]+>/", $fieldContent, $selectTags);

            if (!empty($selectTags[0])) {
                foreach ($selectTags[0] as $selectTag) {
                    $fieldContent = str_replace($selectTag, preg_replace("/class='([^']+)'/", "class='$1 custom-select'", $selectTag), $fieldContent);
                }
            }

        }

        if (preg_match("/type='(radio|checkbox)'/", $fieldContent)) {
            preg_match_all("/(<input[^>]*type='(radio|checkbox)'[^>]+>)\s*<label[^>]+>(.*)<\/label>/misU", $fieldContent, $radioTags);

            if (!empty($radioTags[0])) {
                foreach ($radioTags[0] as $radioIndex => $radioTag) {
                    $inputField = $radioTag;
                    $inputField = str_replace("<input", "<input class='custom-control-input'", $inputField);
                    $inputField = str_replace("<label", "<label class='custom-control-label'", $inputField);

                    $fieldContent = str_replace($radioTag, '<div class="custom-control custom-' . $radioTags[2][$radioIndex] . '">' . $inputField . '</div>', $fieldContent);
                }
            }

        }

        if (preg_match("/type='file'/", $fieldContent)) {
            preg_match_all("/<input[^>]*type='file'[^>]+>/", $fieldContent, $inputFileTags);

            if (!empty($inputFileTags[0])) {
                foreach ($inputFileTags[0] as $inputFileTag) {
                    $inputFileTagBs = preg_replace("/class='([^']+)'/", "class='$1 custom-file-input'", $inputFileTag);

                    $fieldContent = str_replace($inputFileTag, '<label class="custom-file-label">' . __('Choose file', 'raow') . '</label>' . $inputFileTagBs, $fieldContent);
                }
            }

        }

        return $fieldContent;
    }

    public static function buttonClassFrontend($button, $form)
    {
        preg_match("/class='[\.a-zA-Z_ -]+'/", $button, $classes);
        $classes[0] = substr($classes[0], 0, -1);
        $classes[0] .= ' ';
        $classes[0] .= esc_attr($form['button']['class']);
        $classes[0] .= "'";

        $button_pieces = preg_split(
            "/class='[\.a-zA-Z_ -]+'/",
            $button,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        return $button_pieces[0] . $classes[0] . $button_pieces[1];

    }

    public static function inputToButton($button_input, $form)
    {
        preg_match("/<input([^\/>]*)(\s\/)*>/", $button_input, $button_match);

        $button_atts = str_replace("value='" . $form['button']['text'] . "' ", "", $button_match[1]);

        return '<button ' . $button_atts . '>' . $form['button']['text'] . '</button>';
    }

    public function customFields($position)
    {
        if ($position !== 400) {
            return;
        }

        ?>
        <li class="col_xs_setting field_setting">
            <ul>
                <li>
                    <label for="field_col_xs" class="section_label">
                        <?php esc_html_e('Field Size (mobile)', 'raow');?>
                        <?php gform_tooltip('form_field_col_xs');?>
                    </label>
                    <select id="field_col_xs" onchange="SetFieldProperty('colXs', this.value)">
                        <option value="12">12</option>
                        <option value="11">11</option>
                        <option value="10">10</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </li>
            </ul>
        </li>

        <li class="col_md_setting field_setting">
            <ul>
                <li>
                    <label for="field_col_md" class="section_label">
                        <?php esc_html_e('Field Size (tablet)', 'raow');?>
                        <?php gform_tooltip('form_field_col_md');?>
                    </label>
                    <select id="field_col_md" onchange="SetFieldProperty('colMd', this.value)">
                        <option value="">Inherit</option>
                        <option value="12">12</option>
                        <option value="11">11</option>
                        <option value="10">10</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </li>
            </ul>
        </li>

        <li class="col_lg_setting field_setting">
            <ul>
                <li>
                    <label for="field_col_lg" class="section_label">
                        <?php esc_html_e('Field Size (desktop)', 'raow');?>
                        <?php gform_tooltip('form_field_col_lg');?>
                    </label>
                    <select id="field_col_lg" onchange="SetFieldProperty('colLg', this.value)">
                        <option value="">Inherit</option>
                        <option value="12">12</option>
                        <option value="11">11</option>
                        <option value="10">10</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </li>
            </ul>
        </li>
        <?php

    }

    public function customFieldSizes()
    {

        ?>
        <script type="text/javascript">
            jQuery.map(fieldSettings, function (el, i) {
                fieldSettings[i] += ', .col_xs_setting';
                fieldSettings[i] += ', .col_md_setting';
                fieldSettings[i] += ', .col_lg_setting';
            });

            jQuery(document).on('gform_load_field_settings', function (ev, field) {
                jQuery('#field_col_xs').val(field.colXs || '12');
                jQuery('#field_col_md').val(field.colMd || '');
                jQuery('#field_col_lg').val(field.colLg || '');
            });

            // Disable original field size setting
            jQuery(document).ready(function () {
                jQuery('.field_setting.size_setting').remove();
            });
        </script>
        <?php

    }

    public function customFieldTooltips($tooltips)
    {
        $tooltips['form_field_col_xs'] = sprintf(
            '<h6>%s</h6>%s',
            __('Field Size (mobile)', 'raow'),
            __('Select a form field size from the available options. This will set the width of the field on (most) mobile devices and up. If no field sizes are set for larger devices this setting will be inherited.',
                'raow')
        );

        $tooltips['form_field_col_md'] = sprintf(
            '<h6>%s</h6>%s',
            __('Field Size (tablet)', 'raow'),
            __('Select a form field size from the available options. This will set the width of the field on (most) tablet devices and up. If no field sizes are set for larger devices this setting will be inherited.',
                'raow')
        );

        $tooltips['form_field_col_lg'] = sprintf(
            '<h6>%s</h6>%s',
            __('Field Size (desktop)', 'raow'),
            __('Select a form field size from the available options. This will set the width of the field on (most) desktop devices and up.',
                'raow')
        );

        return $tooltips;
    }

    public function formActionOnAjax($formTag, $form)
    {
        if ((defined('DOING_AJAX') && DOING_AJAX) || isset($_POST['gform_ajax'])) {
            preg_match("/action='(.+)(#[^']+)'/", $formTag, $matches);

            $formTag = str_replace($matches[0], 'action="' . $matches[2] . '"', $formTag);
        }

        return $formTag;
    }

    public static function wrapJqueryScriptStart($content = '')
    {
        $backtrace = debug_backtrace();

        if ((defined('DOING_AJAX') && DOING_AJAX) || isset($_POST['gform_ajax']) || $backtrace[3]['function'] != 'get_form') {
            return $content;
        }

        $content = 'document.addEventListener("DOMContentLoaded", function() { ';
        return $content;
    }

    public static function wrapJqueryScriptEnd($content = '')
    {
        $backtrace = debug_backtrace();

        if ((defined('DOING_AJAX') && DOING_AJAX) || isset($_POST['gform_ajax']) || $backtrace[3]['function'] != 'get_form') {
            return $content;
        }

        $content = ' }, false);';
        return $content;
    }
}