<?php
namespace breadhead\mailchimp;

use breadhead\mailchimp\models\MailchimpEventModel;

class MailchimpEvent
{
    const CUSTOMER = 'Customer';
    const CART = 'Cart';
    const ORDER = 'Order';
    const PRODUCT = 'Product';
    const CONCACT = 'Member';

    private $model;

    public static function createEmpty()
    {
        $model = new MailchimpEventModel();

        return new self($model);
    }

    public static function create(int $id)
    {
        $model = MailchimpEventModel::findOne(['id' => $id]);

        if (!$model) {
            throw new \Exception('MailchimpEvent не найден');
        }

        return new self($model);
    }

    private function __construct(MailchimpEventModel $model)
    {
        $this->model = $model;
    }

    public function setEntityId(string $id): self
    {
        $this->model->entity_id = $id;

        return $this;
    }

    public function getEntityId(): string
    {
        return $this->model->entity_id;
    }

    public function setEntityType(string $type): self
    {
        $this->model->entity_type = $type;

        return $this;
    }

    public function getEntityType(): string
    {
        return $this->model->entity_type;
    }

    public function setData(array $data): self
    {
        $this->model->data = json_encode($data);

        return $this;
    }

    public function getData(): array
    {
        return json_decode($this->model->data,true);
    }

    public function getModel(): MailchimpEventModel
    {
        return $this->model;
    }

    public function setStatus(int $status): self
    {
        $this->model->status = $status;

        return $this;
    }

    public function getStatus(): int
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

    public function getEventType(): string
    {
        return $this->model->event_type;
    }

    public function setEventType(string $eventType): self
    {
        $this->model->event_type = $eventType;

        return $this;
    }
}

