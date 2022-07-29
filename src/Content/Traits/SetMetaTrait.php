<?php

namespace OffbeatWP\Content\Traits;

trait SetMetaTrait
{
    /**
     * @param non-empty-string $key Metadata name.
     * @param mixed $value The new metadata value.
     * @return static
     */
    public function setMeta(string $key, $value)
    {
        $this->metaInput[$key] = $value;

        unset($this->metaToUnset[$key]);

        return $this;
    }

    /**
     * @param non-empty-string $key Metadata name.
     * @return static
     */
    public function unsetMeta(string $key)
    {
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }
}