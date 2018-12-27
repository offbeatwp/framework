<?php
if (!isset($settings)) {
    $settings = (object) [];
}

echo raowApp()->container->call([$module, 'render'], [$settings]);