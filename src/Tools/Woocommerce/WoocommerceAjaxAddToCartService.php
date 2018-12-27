<?php
namespace OffbeatWP\Tools\Woocommerce;

use OffbeatWP\Services\AbstractService;

class WoocommerceAjaxAddToCartService extends AbstractService
{
    public function register()
    {
        add_action('wp_ajax_woocommerce_ajax_add_to_cart', array($this, 'ajaxAddToCart'));
        add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', array($this, 'ajaxAddToCart'));
    }

    public function ajaxAddToCart()
    {
        \WC_AJAX::get_refreshed_fragments();

        wp_die();
    }
}
