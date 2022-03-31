<?php

namespace OffbeatWP\Contracts;

interface IWpQuerySubstitute
{
    public function get($property, $default = '');
}