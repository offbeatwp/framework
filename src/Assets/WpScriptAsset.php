<?php

namespace OffbeatWP\Assets;

final class WpScriptAsset
{
    private string $handle;
    private bool $enqueueNow;

    public function __construct(string $handle, bool $enqueueNow) {
        $this->handle = $handle;
        $this->enqueueNow = $enqueueNow;
    }

    /**
     * @see json_encode()
     * @param string $varName Should be a valid JS variable name.
     * @param mixed $data Data will be encodeable with Json.
     * @return void
     */
    public function with(string $varName, $data): void
    {
        if ($this->enqueueNow) {
            WpScriptAsset::addInlineScript($this->handle, $varName, $data);
        } else {
            add_action('wp_enqueue_scripts', function () use ($varName, $data) {
                WpScriptAsset::addInlineScript($this->handle, $varName, $data);
            });
        }
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