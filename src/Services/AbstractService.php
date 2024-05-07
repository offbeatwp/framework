<?php
namespace OffbeatWP\Services;

use OffbeatWP\Foundation\App;

abstract class AbstractService
{
    protected readonly App $app;
    /** @var array<class-string, class-string> */
    public array $bindings = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    abstract public function register(): void;
}