<?php

namespace OffbeatWP\Tools\Woocommerce\Controllers;

use OffbeatWP\Controllers\AbstractController;

class WoocommerceController extends AbstractController
{
    public function actionWoo()
    {
        $data = [];

        echo $this->render('woocommerce/base', $data);
    }
}