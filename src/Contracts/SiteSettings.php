<?php

namespace OffbeatWP\Contracts;

interface SiteSettings {
    /**
     * @param class-string $class
     * @return void
     */
    public function addPage($class);

    /**
     * @param non-empty-string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Should return <b>true</b> on successful update, <b>false</b> on failure
     * @param non-empty-string $key
     * @param mixed $value
     * @return bool
     */
    public function update($key, $value);
}