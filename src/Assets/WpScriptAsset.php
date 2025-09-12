<?php

namespace OffbeatWP\Assets;

use JsonSerializable;
use stdClass;

final class WpScriptAsset
{
    public readonly string $handle;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    /**
     * @see json_encode()
     * @param non-falsy-string $varName Should be a valid JS variable name.
     * @param scalar|mixed[]|stdClass|JsonSerializable $data Data will be encodeable with Json.
     * @return $this
     */
    public function with(string $varName, string|int|float|bool|array|stdClass|JsonSerializable $data): self
    {
        WpScriptAsset::addInlineScript($this->handle, $varName, $data);
        return $this;
    }

    /**
     * @see json_encode()
     * @param non-falsy-string $handle
     * @param string $varName Should be a valid JS variable name.
     * @param scalar|mixed[]|stdClass|JsonSerializable $data Data will be encodeable with Json.
     * @return bool True on success, false on failure.
     */
    public static function addInlineScript(string $handle, string $varName, string|int|float|bool|array|stdClass|JsonSerializable $data): bool
    {
        $encodedData = json_encode($data, JSON_THROW_ON_ERROR);
        return wp_add_inline_script($handle, 'var ' . $varName . ' = ' . $encodedData . ';', 'before');
    }
}
