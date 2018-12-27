<?php
namespace OffbeatWP\Tools\GravityForms;

class Helper {
    public static function mapNameToValue($entry, $form)
    {
       if (is_numeric($form))
            $form = \GFAPI::get_form($form);

        // Setup fields
        $fields = [];

        if(!empty($form['fields'])) {
            foreach ($form['fields'] as $field) {
                if ($field->inputName != '')
                    $fields[$field->id] = $field->inputName;
            }
        }

        // Setup values
        $values = [];
        $values['form_name']        = $form['title'];
        $values['entry_id']         = $entry['id'];
        $values['source_url']       = $entry['source_url'];
        $values['request_datetime'] = $entry['date_created'];
        
        if (is_array($entry) && !empty($fields)) {
            foreach ($fields as $field_id => $field_name) {
                if (!isset($entry[$field_id])) {
                    $field_i      = 1;
                    $field_values = [];

                    while (isset($entry[$field_id . '.' . $field_i])) {
                        if ($entry[$field_id . '.' . $field_i] != '')
                            $field_values[] = $entry[$field_id . '.' . $field_i];

                        $field_i++;
                    }

                    $values[$field_name] = implode(', ', $field_values);
                    continue;
                }

                $values[$field_name] = $entry[$field_id];
            }
        }

        return $values;
    }
}