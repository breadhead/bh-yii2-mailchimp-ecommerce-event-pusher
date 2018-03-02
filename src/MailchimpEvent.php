<?php

namespace breadhead\mailchimp;

use breadhead\mailchimp\models\MailchimpEventModel;

/**
 * Class MailchimpEntityEvent
 * @package MailchimpEventPusher
 */
class MailchimpEvent
{

    const CUSTOMER = 'Customer';
    const CART = 'Cart';
    const ORDER = 'Order';
    const PRODUCT = 'Product';


    private $entity_id = '';
    private $entity_type = '';
    private $event_type = '';
    private $data = [];

    private $model;


    public function __construct($id = null)
    {
        if ($id) {
            if (!$this->model = MailchimpEventModel::findOne(['id' => $id])) {
                throw new \Exception('Mailchimp не найден');
            }
        } else {
            $this->model = new MailchimpEventModel();
        }
    }

    public function setEntityId(string $id)
    {
        $this->model->entity_id = $id;

        return $this;
    }

    public function getEntityId(): string
    {
        return $this->model->entity_id;
    }

    public function setEntityType(string $type)
    {
        $this->model->entity_type = $type;

        return $this;
    }

    public function getEntityType(): string
    {
        return $this->model->entity_type;
    }

    public function setData(array $data)
    {
        $this->model->data = json_encode($data);

        return $this;
    }

    public function getData(): array
    {
        return json_decode($this->model->data,true);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setStatus(int $status): MailchimpEvent
    {
        $this->model->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->model->status;
    }

    public function save()
    {
        if (!$this->model->save()) {
            throw new \Exception('MailchimpEvent not save ' . var_export($this->model->getErrors(), true));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->model->event_type;
    }

    /**
     * @param string $event_type
     * @return $this
     */
    public function setEventType(string $event_type)
    {
        $this->model->event_type = $event_type;

        return $this;
    }
}

