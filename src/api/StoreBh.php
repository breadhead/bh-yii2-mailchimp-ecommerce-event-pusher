<?php
namespace breadhead\mailchimp\api;

class StoreBh extends EcommerceEntity
{

    public function addStore($store_id, $list_id, $name, $currency_code, array $optional_settings = null)
    {
        $optional_fields = ["platform", "domain", "email_address", "money_format", "primary_locale", "timezone", "phone", "address"];

        $data = [
            "id" => $store_id,
            "list_id" => $list_id,
            "name" => $name,
            "currency_code" => $currency_code
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }
        return $this->client->execute("POST", "ecommerce/stores", $data);
    }

    /**
     * Update a store
     *
     * @param string $store_id
     * @param array $data
     */
    public function updateStore($store_id, array $data = [])
    {
        return $this->client->execute("PATCH", "ecommerce/stores/{$store_id}", $data);
    }

    /**
     * Delete a store
     *
     * @param string $string_id
     */
    public function deleteStore($store_id)
    {
        return $this->client->execute("DELETE", "ecommerce/stores/{$store_id}");
    }
}
