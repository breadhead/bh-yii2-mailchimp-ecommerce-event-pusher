<?php
namespace breadhead\mailchimp\api;

class OrdersBh extends EcommerceEntity
{
    public function createOrder(string $storeId, array $data)
    {
        return $this->client->execute("POST", "ecommerce/stores/{$storeId}/orders/", $data);
    }

    public function getOrders($store_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/orders", $query);
    }

    public function getOrder($store_id, $order_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/orders/{$order_id}", $query);
    }

    public function addOrder($store_id, $order_id, $currency_code, $order_total, array $customer = [], array $lines = [], array $optional_settings = null)
    {
        $optional_fields = ["campaign_id", "financial_status", "tax_total", "shipping_total", "tracking_code", "processed_at_foreign", "updated_at_foreign", "cancelled_at_foreign", "shipping_address", "billing_address"];
        $data = [
            "id" => $order_id,
            "customer" => $customer,
            "currency_code" => $currency_code,
            "order_total" => $order_total,
            "lines" => $lines
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, $this->client->optionalFields($optional_fields, $optional_settings));
        }
        return $this->client->execute("POST", "ecommerce/stores/{$store_id}/orders/", $data);
    }

    public function updateOrder($store_id, $order_id, array $data = [])
    {
        return $this->client->execute("PATCH", "ecommerce/stores/{$store_id}/orders/{$order_id}", $data);
    }

    public function getOrderLines($store_id, $order_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines", $query);
    }

    public function getOrderLine($store_id, $order_id, $line_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/{$line_id}", $query);
    }

    public function addOrderLine($store_id, $order_id, $line_id, $product_id, $product_variant_id, $quantity, $price)
    {
        $data = [
            "id" => $line_id,
            "product_id" => $product_id,
            "product_variant_id" => $product_variant_id,
            "quantity" => $quantity,
            "price" => $price
        ];
        return $this->client->execute("POST", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/", $data);
    }

    public function updateOrderLine($store_id, $order_id, $line_id, array $data = [])
    {
        return $this->client->execute("PATCH", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/{$line_id}", $data);
    }

    public function deleteOrderLine($store_id, $order_id, $line_id)
    {
        return $this->client->execute("DELETE", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/{$line_id}");
    }

    public function deleteOrder($store_id, $order_id)
    {
        return $this->client->execute("DELETE", "ecommerce/stores/{$store_id}/orders/{$order_id}");
    }
}
