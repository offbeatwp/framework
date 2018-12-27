<?php

namespace OffbeatWP\Tools\BreadcrumbNavXT;

use OffbeatWP\Contracts\View;
use OffbeatWP\Services\AbstractService;

class Service extends AbstractService {
    public function register(View $view )
    {
        $view->registerGlobal('bcn', new Helpers\ViewHelpers());
    }
}