<?php

namespace OffbeatWP\Services;

abstract class AbstractServicePageBuilder extends AbstractService
{
    public function register(): void
    {
        if (method_exists($this, 'afterRegister')) {
            $this->app->container->call([$this, 'afterRegister']);
        }
    }
}
