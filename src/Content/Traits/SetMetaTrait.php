<?php

namespace OffbeatWP\Content\Traits;

trait SetMetaTrait
{
    /**
     * @param string $key Metadata name.
     * @param scalar|mixed[] $value The new metadata value.
     * @return $this
     */
    public function setMeta(string $key, $value): self
    {
        $this->metaInput[$key] = $value;

        unset($this->metaToUnset[$key]);

        return $this;
    }

    /**
     * @param string $key Metadata name.
     * @return $this
     */
    public function unsetMeta(string $key): self
    {
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }
}