<?php
namespace breadhead\mailchimp;

use breadhead\mailchimp\api\MailchimpClient;
use breadhead\mailchimp\models\MailchimpEventModel;
use breadhead\mailchimp\api\CartsBh;
use breadhead\mailchimp\api\CustomerBh;
use breadhead\mailchimp\api\OrdersBh;
use breadhead\mailchimp\api\ProductsBh;
use MailChimp\MailChimp;
use Psr\Http\Message\ResponseInterface;
use yii\db\ActiveRecord;

class MailchimpEventSender
{
    private $storeId;

    private static $methods = [
        'afterInsert' => [
            'Customer' => 'createCustomer',
            'Cart' => 'createCart',
            'Product' => 'createProduct',
            'Order' => 'createOrder'
        ],
        'afterUpdate' => [
            'Customer' => 'upsertCustomer',
            'Cart' => 'updateCart',
            'Product' => 'updateProduct',
            'Order' => 'updateOrder'
        ],
        'afterDelete' => [
            'Customer' => 'deleteCustomer',
            'Cart' => 'deleteCart',
            'Product' => 'deleteProduct',
            'Order' => 'deleteOrder'
        ],
    ];
    private $client;

    public function __construct(MailchimpClient $client, string $storeId)
    {
        $this->client = $client;
        $this->storeId = $storeId;
    }

    private function getProducts()
    {
        return new ProductsBh($this->client);
    }

    private function getCarts()
    {
        return new CartsBh($this->client);
    }

    private function getCustomers()
    {
        return new CustomerBh($this->client);
    }

    private function getOrders()
    {
        return new OrdersBh($this->client);
    }

    public function sendEvent(MailchimpEvent $event)
    {
        if ($this->getEventMethod($event)) {
            /** @var ResponseInterface $response */
            $response = $this->makeCall(
                $this->getObject($event->getEntityType()),
                $this->getEventMethod($event),
                $this->getData($event)
            );

            $status = $response->getStatusCode() == 200 ? MailchimpEventModel::DONE : MailchimpEventModel::ERROR;

            $event->setStatus($status)->save();

            $this->checkIfNeedCreate($response, $event);
        }
    }

    private function makeCall($object, string $method, array $args)
    {
        $answer = call_user_func_array(array($object, $method), $args);

        $date = date('dmY');

        if ((isset($answer->status) && (int)$answer->status) > 0) {
            \Yii::error(
                'REQUEST '.$method . json_encode((array)$args),
                'mailchimp'
            );
            \Yii::error(
                'ANSWER ' . json_encode((array)$answer),
                'mailchimp'
            );
        } else {
            \Yii::info(
                'REQUEST '.$method . json_encode((array)$args),
                'mailchimp'
            );
        }

        \Yii::trace(date('H:i', time()) . '  REQUEST ' . $method . json_encode((array)$args) . "\n" . date('H:i', time()) . '  ANSWER ' . json_encode((array)$answer) . "\n", 'mailchimp');

        return $answer;
    }

    private function getObject(string $entity_type)
    {
        switch ($entity_type) {
            case MailchimpEvent::ORDER:
                $object = $this->getOrders();

                break;
            case MailchimpEvent::CUSTOMER:
                $object = $this->getCustomers();

                break;
            case MailchimpEvent::CART:
                $object = $this->getCarts();

                break;
            case MailchimpEvent::PRODUCT:
                $object = $this->getProducts();

                break;
            default:
                throw new \InvalidArgumentException('Unexpected entity_type');
        }

        return $object;
    }

    private function getEventMethod(MailchimpEvent $event)
    {
        return isset(self::$methods[$event->getEventType()]) && isset(self::$methods[$event->getEventType()][$event->getEntityType()]) ? self::$methods[$event->getEventType()][$event->getEntityType()] : false;
    }

    private function getData(MailchimpEvent $event)
    {
        $data = [$this->storeId];

        switch ($event->getEventType()) {
            case ActiveRecord::EVENT_AFTER_DELETE:
                $data[] = $event->getEntityId();

                break;
            case ActiveRecord::EVENT_AFTER_UPDATE:
                $data[] = $event->getEntityId();
                $data[] = $event->getData();

                break;
            case ActiveRecord::EVENT_AFTER_INSERT:
                $data[] = $event->getData();

                break;
        }

        return $data;
    }

    private function checkIfNeedCreate($response, MailchimpEvent $event)
    {
        if ($event->getEventType() == MailchimpEventModel::EVENT_AFTER_UPDATE
            && ($response->getStatusCode() == '404')
        ) {
            $event->setEventType(ActiveRecord::EVENT_AFTER_INSERT)
                ->setStatus(MailchimpEventModel::NEW)
                ->save();
        }
    }
}
