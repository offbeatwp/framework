<?php

namespace OffbeatWP\Routes;

interface IMiddleware
{
    public function handle(): bool;
}