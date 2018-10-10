<?php
namespace breadhead\mailchimp\api;

use MailChimp\Ecommerce\Carts;
use yii\caching\FileCache;

class CartsBh extends Carts
{
    public function createCart(string $storeId, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$storeId}/carts", $data);
    }

    public function getCart($storeId, $cartId, array $query = [])
    {
        $cacheKey = 'carts_' . $storeId . '_' . $cartId;
        $obCache = (new FileCache());

        $answer = $obCache->get($cacheKey);
        if (!$answer) {
            $answer = self::execute("GET", "ecommerce/stores/{$storeId}/carts/{$cartId}", $query);

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

            return parent::updateCart($storeId, $cartId, $data);

        } elseif ($externalCart) {
            return $this->deleteCart($storeId, $cartId);
        }
    }
}
