<?php

namespace OffbeatWP\Tools\Woocommerce\Repositories;

use OffbeatWP\Repositories\PostsRepository;
use OffbeatWP\Tools\Woocommerce\Content\Product;

class ProductsRepository extends PostsRepository
{
    const POST_TYPE     = 'product';
    const ORDER_BY      = 'menu_order';
    const ORDER         = 'ASC';
    const POST_CLASS    = Product::class;
}
