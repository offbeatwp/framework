<?php

namespace OffbeatWP\Contracts;

interface SiteSettings {
    /** @param class-string $class */
    public function addPage(string $class): void;

    /** @param string $key */
    public function get(string $key): mixed;

    /** Should return <b>true</b> on successful update, <b>false</b> on failure */
    public function update(string $key, mixed $value): bool;
}