<?php

namespace OffbeatWP\Fields;

class Helper {
    public static function getDefaults($entries, &$fieldDefaults = [])
    {
        foreach ($entries as $entry) {
            if (isset($entry['name'])) {
                $fieldDefaults[$entry['name']] = (isset($entry['default'])) ? $entry['default'] : null;

                continue;
            } elseif (is_array($entry)) {
                self::getDefaults($entry, $fieldDefaults);
            }
        }

        return $fieldDefaults;
    }
}