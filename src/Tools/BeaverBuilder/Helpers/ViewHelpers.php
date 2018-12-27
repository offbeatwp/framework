<?php

namespace OffbeatWP\Tools\BeaverBuilder\Helpers;

class ViewHelpers
{
    public function isEnabled()
    {
        return (class_exists('\FLBuilderModel') && \FLBuilderModel::is_builder_enabled());
    }
}
