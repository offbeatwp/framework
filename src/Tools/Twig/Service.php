<?php
namespace OffbeatWP\Tools\Twig;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Tools\Twig\TwigView;
use OffbeatWP\Contracts\View;

class Service extends AbstractService {

    public $bindings = [
        View::class => TwigView::class
    ];
}