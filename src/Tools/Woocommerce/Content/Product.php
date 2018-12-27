<?php

namespace OffbeatWP\Tools\Woocommerce\Content;

use OffbeatWP\Content\Post;

class Product extends Post
{
    protected $wc_product;

    public function woo()
    {
        if (isset($this->wc_product)) {
            return $this->wc_product;
        }

        $this->wc_product = wc_get_product($this->ID);

        return $this->wc_product;
    }

    public function getAttribute($name)
    {
        if (!$this->hasPost()) {
            return false;
        }

        switch ($name) {
            case 'sku':
                return $this->woo()->get_sku();
                break;
            case 'regularPrice':
                return $this->woo()->get_regular_price();
                break;
            case 'isInStock':
                return $this->woo()->is_in_stock();
                break;
            case 'stockQuantity':
                return $this->woo()->get_stock_quantity();
                break;
            case 'managingStock':
                return $this->woo()->managing_stock();
                break;
            case 'thumbnail':
                if (has_post_thumbnail($this->post)) {
                    return wp_get_attachment_url(get_post_thumbnail_id($this->post->ID));
                }
                return wc_placeholder_img_src();
                break;
        }

        return parent::getAttribute($name);
    }

    public function inStock()
    {
        if (!$this->woo()->is_in_stock()) {
            return false;
        }

        if ($this->woo()->managing_stock()) {

            if ($this->woo()->get_stock_quantity() == 0 ||
                $this->woo()->get_regular_price() == '') {
                return false;
            }
        }

        return true;
    }

    public function getPriceHtml()
    {
        return $this->woo()->get_price_html();
    }

    public function getVariationAttributes()
    {
        $variationAttributes   = [];
        $product               = $this->woo();
        $wcVariationAttributes = $product->get_variation_attributes();

        if (!empty($wcVariationAttributes)) {
            foreach ($wcVariationAttributes as $attributeKey => $attributeOptions) {
                $variationAttributeOptions = [];

                $options    = $wcVariationAttributes[ $attributeKey ];

                if (!empty($attributeOptions)) {
                    if ($product && taxonomy_exists($attributeKey)) {
                        // Get terms if this is a taxonomy - ordered. We need the names too.
                        $terms = wc_get_product_terms($product->get_id(), $attributeKey, array(
                            'fields' => 'all',
                        ));

                        foreach ($terms as $term) {
                            $variationAttributeOptions[] = [
                                'label' => $term->name,
                                'value' => $term->slug,
                            ];
                        }
                    } else {
                        foreach ($options as $option) {
                            $variationAttributeOptions[] = [
                                'label' => esc_html(apply_filters('woocommerce_variation_option_name', $option)),
                                'value' => $option,
                            ];
                        }
                    }
                }

                $variationAttributes[] = [
                    'key'        => $attributeKey,
                    'label'      => wc_attribute_label($attributeKey),
                    'attributes' => $variationAttributeOptions,
                ];
            }
        }

        // var_dump($variationAttributes);

        return $variationAttributes;
    }
}
