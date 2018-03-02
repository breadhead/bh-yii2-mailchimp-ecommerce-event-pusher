<?php
/**
 * Created by PhpStorm.
 * User: kukushkina
 * Date: 28.02.18
 * Time: 16:33
 */


namespace breadhead\mailchimp;

use breadhead\mailchimp\models\MailchimpEventModel;
use breadhead\mailchimp\api\CartsBh;
use breadhead\mailchimp\api\CustomerBh;
use breadhead\mailchimp\api\OrdersBh;
use breadhead\mailchimp\api\ProductsBh;
use MailChimp\MailChimp;
use yii\db\ActiveRecord;

class MailChimpEventSender
{
    private $mcAgent;
    private $store_id;


    private static $methods = [
        'afterInsert' => [
            'Customer' => 'createCustomer',
            'Cart' => 'createCart',
            'Product' => 'createProduct',
            'Order' => 'createOrder'
        ],
        'afterUpdate' => [
            'Customer' => 'updateCustomer',
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

    /**
     * MailchimpManager constructor.
     * @param $store_id
     */
    public function __construct(string $store_id)
    {
        $this->mcAgent = new MailChimp();

        $this->store_id = $store_id;
    }

    /**
     * getProducts
     * @return ProductsBh
     */
    private function getProducts()
    {
        return new ProductsBh();
    }

    /**
     * getCarts
     * @return CartsBh
     */
    private function getCarts()
    {
        return new CartsBh();
    }

    /**
     * getCustomers
     * @return \MailChimp\Ecommerce\Customers
     */
    private function getCustomers()
    {
        return new CustomerBh();
    }

    /**
     * getOrders
     * @return OrdersBh
     */
    private function getOrders()
    {
        return new OrdersBh();
    }

    /**
     * @param MailchimpEvent $event
     */
    public function sendEvent(MailchimpEvent $event)
    {
        $response = $this->makeCall($this->getObjects($event->getEntityType()), $this->getEventMethod($event), $this->getData($event));

        $event->setStatus(MailchimpEventModel::DONE)->save();

        $this->checkCreate($response, $event);
    }


    /**
     * @param $object
     * @param $method
     * @param $args
     * @return mixed|null
     */
    private function makeCall($object, $method, $args)
    {
        $response = call_user_func_array(array($object, $method), $args);

        \Yii::trace(date('H:i', time()) . '  REQUEST ' . $method . json_encode((array)$args) . "\n" . date('H:i', time()) . '  ANSWER ' . json_encode((array)$answer) . "\n", 'mailchimp');

        return $response;
    }

    /**
     * @param $entity_type
     * @return CartsBh|OrdersBh|ProductsBh|\MailChimp\Ecommerce\Customers
     * @throws \Exception
     */
    private function getObjects(string $entity_type)
    {
        switch ($entity_type) {
            case MailchimpEvent::ORDER:
                $objects = $this->getOrders();
                break;
            case MailchimpEvent::CUSTOMER:
                $objects = $this->getCustomers();
                break;
            case MailchimpEvent::CART:
                $objects = $this->getCarts();
                break;
            case MailchimpEvent::PRODUCT:
                $objects = $this->getProducts();
                break;
            default:
                throw new \Exception('Unexpected entity_type');
        }

        return $objects;
    }

    /**
     * @param MailchimpEvent $event
     * @return mixed
     */
    private function getEventMethod(MailchimpEvent $event)
    {
        return self::$methods[$event->getEventType()][$event->getEntityType()];
    }

    /**
     * @param MailchimpEvent $event
     * @return array
     */
    private function getData(MailchimpEvent $event)
    {
        $data = [];
        $data[] = $this->store_id;

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

    /**
     * @param $response
     * @param MailchimpEvent $event
     */
    private function checkCreate($response, MailchimpEvent $event)
    {
        if (isset($response->title) && ($event->getEventType() == MailchimpEventModel::EVENT_AFTER_UPDATE) && ($response->title == 'Resource Not Found')) {
            $event->setEventType(ActiveRecord::EVENT_AFTER_INSERT)->setStatus(MailchimpEventModel::NEW)->save();
        }
    }
}