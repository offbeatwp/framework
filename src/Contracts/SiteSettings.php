<?php

namespace OffbeatWP\Contracts;

interface SiteSettings {
    /** @param class-string $class */
    public function addPage($class);

    /** @param string $key */
    public function get($key);

    /**
     * Should return <b>true</b> on successful update, <b>false</b> on failure
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function update($key, $value);
}