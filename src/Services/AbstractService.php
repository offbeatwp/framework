<?php
namespace OffbeatWP\Services;

abstract class AbstractService {
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
}