<?php
/**
 * Created by PhpStorm.
 * User: kukushkina
 * Date: 01.03.18
 * Time: 19:42
 */

namespace bh\mailchimp\api;


use MailChimp\Ecommerce\Customers;

class CustomerBh extends Customers
{
    public function createCustomer(string $store_id, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$store_id}/orders/", $data);
    }
}