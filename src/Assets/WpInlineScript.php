<?php

namespace OffbeatWP\Assets;

use stdClass;

final class WpInlineScript
{
    private string $handle;

    public function __construct(string $handle) {
        $this->handle = $handle;
    }

    /**
     * Data <b>must</b> be encodeable with Json
     * @param string $varName
     * @param scalar|array|stdClass $data
     * @return void
     */
    public function with(string $varName, $data): bool
    {
        return WpInlineScript::add($this->handle, $varName, $data);
    }

    /**
     * @param string $handle
     * @param string $varName
     * @param scalar|array|stdClass $data
     * @return bool True on success, false on failure.
     */
    public static function add(string $handle, string $varName, $data): bool
    {
        $encodedData = json_encode($data, is_object($data) ? JSON_THROW_ON_ERROR|JSON_FORCE_OBJECT : JSON_THROW_ON_ERROR);
        return wp_add_inline_script($handle, 'var ' . $varName . ' = ' . $encodedData . ';', 'before');
    }
}