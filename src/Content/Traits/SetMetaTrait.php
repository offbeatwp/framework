<?php

namespace OffbeatWP\Content\Traits;

use Serializable;
use stdClass;

trait SetMetaTrait
{
    /**
     * @param string $key Metadata name.
     * @param string|int|float|bool|mixed[]|\stdClass|\Serializable $value The new metadata value.
     * @return $this
     */
    final public function setMeta(string $key, string|int|float|bool|array|stdClass|Serializable $value)
    {
        $this->metaInput[$key] = $value;

        unset($this->metaToUnset[$key]);

        return $this;
    }

    /**
     * @param string $key Metadata name.
     * @return $this
     */
    final public function unsetMeta(string $key)
    {
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }
}