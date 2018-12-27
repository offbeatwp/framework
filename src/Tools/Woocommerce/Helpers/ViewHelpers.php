<?php

namespace OffbeatWP\Tools\Woocommerce\Helpers;

class ViewHelpers
{
    public function WC()
    {
        return WC();
    }

    public function content()
    {
        ob_start();
        woocommerce_content();
        return ob_get_clean();
    }

    public function getCurrencySymbol()
    {
        return get_woocommerce_currency_symbol();
    }

    public function formatPrice($price)
    {
        return wc_price($price);
    }

    public function attributeLabel($attributeName)
    {
        return wc_attribute_label($attributeName);
    }

    public function getProductTerms($productId, $attribute, $args = [])
    {
        return wc_get_product_terms( $productId, $attribute, $args );
    }

    public function quantityInput($product = null)
    {
        woocommerce_quantity_input( array(
            'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
            'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
            'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
        ) );
    }

    public function getShopUrl()
    {
        return get_permalink( wc_get_page_id( 'shop' ) );
    }

    public function getCartUrl()
    {
        return wc_get_cart_url();
    }

    public function getCheckoutUrl()
    {
        return wc_get_checkout_url();
    }

    public function getMyAccountUrl()
    {
        return get_permalink(get_option('woocommerce_myaccount_page_id'));
    }
}