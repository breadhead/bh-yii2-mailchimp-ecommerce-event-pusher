<?php
namespace breadhead\mailchimp\api;

class CustomerBh extends EcommerceEntity
{
    public function createCustomer(string $storeId, array $data)
    {
        return $this->client->execute("POST", "ecommerce/stores/{$storeId}/customers/", $data);
    }

    public function getCustomers($store_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/customers", $query);
    }

    public function getCustomer($store_id, $customer_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/customers/{$customer_id}", $query);
    }

    public function addCustomer($store_id, $customer_id, $email_address, $opt_in_status, array $optional_settings = null)
    {
        $optional_fields = ["company", "first_name", "last_name", "orders_count", "vendor", "total_spent", "address"];

        $data = array(
            "id" => $customer_id,
            "email_address" => $email_address,
            "opt_in_status" => $opt_in_status
        );

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, $this->client->optionalFields($optional_fields, $optional_settings));
        }

        return $this->client->execute("POST", "ecommerce/stores/{$store_id}/customers/", $data);
    }

    public function updateCustomer($store_id, $customer_id, array $data = [] )
    {
        return $this->client->execute("PATCH", "ecommerce/stores/{$store_id}/customers/{$customer_id}", $data);
    }

    public function upsertCustomer($store_id, $customer_id, array $data = [] )
    {
        return $this->client->execute("PUT", "ecommerce/stores/{$store_id}/customers/{$customer_id}", $data);
    }

    public function deleteCustomer($store_id, $customer_id)
    {
        return $this->client->execute("DELETE", "ecommerce/stores/{$store_id}/customers/{$customer_id}");
    }
}
