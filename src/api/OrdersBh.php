<?php
namespace breadhead\mailchimp\api;

use MailChimp\Ecommerce\Orders;

class OrdersBh extends Orders
{
    public function createOrder(string $storeId, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$storeId}/orders/", $data);
    }
}
