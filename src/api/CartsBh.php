<?php
namespace breadhead\mailchimp\api;

use MailChimp\Ecommerce\Carts;

class CartsBh extends Carts
{
    public function createCart(string $storeId, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$storeId}/carts", $data);
    }

    public function updateCart($storeId, $cartId, array $data = [])
    {
        $externalCart = $this->getCart($storeId, $cartId);

        if (!$externalCart) {
            return $this->createCart($storeId, $data);
        } elseif (count($data['lines']) > 0) {
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

            return $this->updateCart($storeId, $cartId, $data);

        } elseif ($externalCart) {
            return $this->deleteCart($storeId, $cartId);

        }
    }
}
