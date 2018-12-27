<?php

namespace OffbeatWP\Tools\Woocommerce;

use OffbeatWP\Controllers\Controllers;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Tools\Woocommerce\Controllers\WoocommerceController;
use OffbeatWP\Tools\Woocommerce\Repositories\ProductsRepository;
use OffbeatWP\Contracts\View;

class WoocommerceService extends AbstractService
{

    public $bindings = [
        ProductsRepository::class => ProductsRepository::class
    ];

    public function register(View $view)
    {
        if (!function_exists('is_woocommerce')) return null;

        add_theme_support('woocommerce');

        add_filter('woocommerce_template_loader_files', function($templates) {
            $templates = ['index.php'];
            return $templates;
        });

        offbeat('routes')->register([WoocommerceController::class, 'actionWoo'],
            function () {
                return is_woocommerce();
            }
        );

        $view->registerGlobal('wc', new Helpers\ViewHelpers());

        add_filter( 'style_loader_tag', [$this, 'deferStyles'], 10, 4);
    }

    public function deferStyles($tag, $handle, $href, $media)
    {
        if (strpos($handle, 'woocommerce') === 0) {
            $tag = str_replace('rel=\'stylesheet\'', 'rel=\'preload\'', $tag);
        }

        return $tag;
    }
}
