<?php

namespace OffbeatWP\Tools\Polylang;

use OffbeatWP\Contracts\View;
use OffbeatWP\Services\AbstractService;

class Service extends AbstractService {
    public function register(View $view )
    {
        $view->registerGlobal('polylang', new Helpers\ViewHelpers());
    }
}