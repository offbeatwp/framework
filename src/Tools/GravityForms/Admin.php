<?php

namespace OffbeatWP\Tools\GravityForms;

class Admin {
    public function buttonClass($form_settings, $form)
    {

        $form_settings["Form Button"]["button_class"] = '
            <tr id="form_button_text_setting" class="child_setting_row" style="' . $text_style_display . '">
                <th>
                    ' . __('Button Class', 'gravityforms') . ' ' . gform_tooltip('form_button_class', '', true) . '
                </th>
                <td>
                    <input type="text" id="form_button_text_class" name="form_button_text_class" class="fieldwidth-3" value="' . esc_attr(rgars($form,
                'button/class')) . '" />
                </td>
            </tr>';

        return $form_settings;
    }

    public function buttonClassProcess($updated_form)
    {
        $updated_form['button']['class'] = rgpost('form_button_text_class');
        return $updated_form;
    }
}