<?php
namespace breadhead\mailchimp\api;

use MailChimp\Ecommerce\Customers;

class CustomerBh extends Customers
{
    public function createCustomer(string $storeId, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$storeId}/customers/", $data);
    }
}
