# mailchimp-event-pusher
Send events to mailchimp ecommerce


do  migrations:

yii migrate --migrationPath=@bh/bh-yii2-mailchimp-ecommerce-event-pusher/migrations/

config:

    
    'components' => [
        'mailchimpEventPusher' => function () {
            $store_id = <Your mailchimp store id>;
            return new \bh\mailchimp\MailchimpEventPusher($store_id);
        }
    ]
    
    
    
Implement your model class from MailchimpEventInterface. This ecommerce module for tracking:

-Cart

-Order

-Customer

-Product

Example of realization:

        public function saveMailchimpEvent(string $event_type): MailchimpEvent
            {
                $event = (new MailchimpEvent())->setEntityId($this->id)->setEntityType(MailchimpEvent::CUSTOMER)->setEventType($event_type)->setData($this->getMailchimpData())->save();
        
                return $event;
            }
        
            public function getMailchimpData()
            {
                return [
                    'id' => (string)$this->id,
                    "email_address" =>(string)$this->email,
                    'opt_in_status' => (bool)$this->subscribe,
                    'first_name' => (string)$this->name,
                    'last_name' => (string)$this->last_name,
                    'orders_count' => count($this->orders),
                    'total_spent' => (float)OrderModel::find()->where(['status' => OrderModel::PAYED, 'customer_id' => $this->id])->sum('total')
                ];
            }

        

For sending events:
    
    \Yii::$app->mailchimpEventPusher->getManager()->sendEvent($event)
