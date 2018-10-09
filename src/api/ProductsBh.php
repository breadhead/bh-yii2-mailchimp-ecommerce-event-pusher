<?php
namespace breadhead\mailchimp\api;

use MailChimp\Ecommerce\Products;

class ProductsBh extends Products
{
    public function updateProduct($storeId, $productId, array $data = [])
    {
        return self::execute("PATCH", "ecommerce/stores/{$storeId}/products/{$productId}", $data);
    }

    public function createProduct($storeId, array $data = [])
    {
        return self::execute("POST", "ecommerce/stores/{$storeId}/products", $data);

    }
}
