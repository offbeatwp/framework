<?php
if (!isset($settings)) {
    $settings = (object) [];
}

echo offbeat()->container->call([$module, 'render'], [$settings]);