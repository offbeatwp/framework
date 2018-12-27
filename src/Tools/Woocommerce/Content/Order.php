<?php

namespace OffbeatWP\Tools\Woocommerce\Content;

use OffbeatWP\Content\Post;

class Order extends Post
{
    protected $wcOrder;

    public function woo()
    {
        if (isset($this->wcOrder)) {
            return $this->wcOrder;
        }

        $this->wcOrder = wc_get_order($this->ID);

        return $this->wcOrder;
    }

    public function getAttribute($name)
    {
        if (!$this->hasPost()) {
            return false;
        }

        switch ($name) {
            case 'items':
                return $this->woo()->get_items();
                break;
            case 'quantity':
                $quantity = 0;
                foreach ($this->woo()->get_items() as $item) {
                    $quantity = $quantity + (1 * $item['qty']);
                }
                return (int) $quantity;
                break;
            case 'total':
                return $this->woo()->get_total();
                break;
            case 'billingCompany':
                return $this->woo()->get_billing_company();
                break;
            case 'shippingCompany':
                return $this->woo()->get_shipping_company();
                break;
            case 'shippingMethod':
                $shippingMethod = array_shift($this->woo()->get_shipping_methods())['method_id'];

                return $shippingMethod;
                break;
        }

        return parent::getAttribute($name);
    }
}