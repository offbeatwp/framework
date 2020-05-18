<?php

namespace OffbeatWP\Contracts;

interface SiteSettings {
    public function addPage($class);

    public function get($key);

    public function update($key, $value);
}