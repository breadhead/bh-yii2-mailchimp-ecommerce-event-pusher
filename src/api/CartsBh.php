<?php
namespace breadhead\mailchimp\api;

use yii\caching\FileCache;

class CartsBh extends EcommerceEntity
{
    public function createCart(string $storeId, array $data)
    {
        return $this->client->execute("POST", "ecommerce/stores/{$storeId}/carts", $data);
    }

    public function getCart($storeId, $cartId, array $query = [])
    {
        $cacheKey = 'carts_' . $storeId . '_' . $cartId;

        $obCache = (new FileCache());

        $answer = $obCache->get($cacheKey);
        if (!$answer) {
            $answer = $this->client->execute("GET", "ecommerce/stores/{$storeId}/carts/{$cartId}", $query);

            if (!$answer || $answer->status == 404) {
                $obCache->delete($cacheKey);
                $answer = null;
            } else {
                $obCache->add($cacheKey, $answer, 60);
            }

        };

        return $answer;
    }

    public function updateCart($storeId, $cartId, array $data = [])
    {
        $externalCart = $this->getCart($storeId, $cartId);

        if (!$externalCart) {
            return $this->createCart($storeId, $data);
        } elseif (count($data['lines']) > 0) {
            if ($externalCart->lines) {
                foreach ($externalCart->lines as $line) {
                    $found = false;
                    foreach ($data['lines'] as $item) {
                        if ($item['id'] == $line->id) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $this->deleteCartLine($storeId, $cartId, $line->id);
                    }
                }
            }

            return parent::patchCart($storeId, $cartId, $data);

        } elseif ($externalCart) {
            return $this->deleteCart($storeId, $cartId);
        }
    }

    public function getCarts($store_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/carts", $query);
    }
    
    
    public function patchCart($store_id, $cart_id, array $data = [])
    {
        return $this->client->execute("PATCH", "ecommerce/stores/{$store_id}/carts/{$cart_id}", $data);
    }
    
    public function getCartLines($store_id, $cart_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines", $query);
    }
    
    public function getCartLine($store_id, $cart_id, $line_id, array $query = [])
    {
        return $this->client->execute("GET", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/{$line_id}", $query);
    }
    
    public function addCartLine($store_id, $cart_id, $line_id, $product_id, $product_variant_id, $quantity, $price)
    {
        $data = [
            "id" => $line_id,
            "product_id" => $product_id,
            "product_variant_id" => $product_variant_id,
            "quantity" => $quantity,
            "price" => $price
        ];
        return $this->client->execute("POST", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/", $data);
    }

    
    public function updateCartLine($store_id, $cart_id, $line_id, array $data = [])
    {
        return $this->client->execute("PATCH", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/{$line_id}", $data);
    }


    public function deleteCartLine($store_id, $cart_id, $line_id)
    {
        return $this->client->execute("DELETE", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/{$line_id}");
    }
    
    public function deleteCart($store_id, $cart_id)
    {
        return $this->client->execute("DELETE", "ecommerce/stores/{$store_id}/carts/{$cart_id}");
    }
}
