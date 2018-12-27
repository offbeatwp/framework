<?php

namespace OffbeatWP\Contracts;

interface SiteSettings {
    public function register();

    public function addSection($class);

    public function get($key);

    public function update($key, $value);
}