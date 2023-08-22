<?php
namespace OffbeatWP\Services;

use OffbeatWP\Foundation\App;

abstract class AbstractService {
    /** @var App */
    protected $app;

    /** @param App $app */
    public function __construct($app)
    {
        $this->app = $app;
    }
}