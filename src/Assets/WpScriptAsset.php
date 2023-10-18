<?php

namespace OffbeatWP\Assets;

final class WpScriptAsset
{
    private string $handle;

    public function __construct(string $handle) {
        $this->handle = $handle;
    }

    /**
     * @see json_encode()
     * @param string $varName Should be a valid JS variable name.
     * @param mixed $data Data will be encodeable with Json.
     * @return void
     */
    public function with(string $varName, $data): bool
    {
        return WpScriptAsset::addInlineScript($this->handle, $varName, $data);
    }

    /**
     * @see json_encode()
     * @param string $handle
     * @param string $varName Should be a valid JS variable name.
     * @param mixed $data Data will be encodeable with Json.
     * @return bool True on success, false on failure.
     */
    public static function addInlineScript(string $handle, string $varName, $data): bool
    {
        $encodedData = json_encode($data, JSON_THROW_ON_ERROR);
        return wp_add_inline_script($handle, 'var ' . $varName . ' = ' . $encodedData . ';', 'before');
    }
}