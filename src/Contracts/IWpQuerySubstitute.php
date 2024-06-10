<?php

namespace OffbeatWP\Contracts;

interface IWpQuerySubstitute
{
    /**
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    public function get($property, $default = '');
}
