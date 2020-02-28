# mailchimp-event-pusher
Send events to mailchimp ecommerce

do  migrations:

php yii migrate --migrationPath=@vendor/breadhead/yii2-mailchimp-ecommerce-event-pusher/src/migrations/

    
Implement your model class from MailchimpEventInterface. This ecommerce module for tracking:

- Cart
- Order
- Customer and Contact
- Product

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
      
config:

    'components' => [
        'mailchimpeventpusher' => function () {
            $storeId = <Your mailchimp store id>;
            $mailchimpApiKey = <Your mailchimp api key>;
            $listId = <Your mailchimp list id>;
    
            $client = new \breadhead\mailchimp\api\MailchimpClient($mailchimpApiKey);
            $sender = new \breadhead\mailchimp\MailchimpEventSender($client, $storeId, $listId);

            return new \breadhead\mailchimp\MailchimpEventPusher($storeId, $sender);
        }
    ]
    
To send events code example:

    class MailchimpController extends Controller
    {
        private $maxEventsPerStep;

        ...

        public function actionSendevents(): void
        {
            $jobs = $this->defineJobs();

            $this->doJobs($jobs);
        }

        private function doJobs($jobs): bool
        {
            try {
                $eventSender = \Yii::$app->mailchimpeventpusher->getManager();

                foreach ($jobs as $event) {
                    $event->status = MailchimpEventModel::RUN;
                    Helper::saveAndLogModel($event);

                    $mailchimpEvent = MailchimpEvent::create($event->id);

                    /* @var MailchimpEventSender $eventSender */
                    $eventSender->sendEvent($mailchimpEvent);
                }
            } catch (\Exception $e) {

                throw $e;
            }

            return true;
        }

        private function defineJobs(): array
        {
            if (MailchimpEventModel::findOne(['status' => MailchimpEventModel::RUN])) {
                return [];
            }

            return MailchimpEventModel::find()
                ->where(['status' => MailchimpEventModel::NEW])
                ->orderBy(['created_at' => 'ASC'])
                ->limit($this->maxEventsPerStep)
                ->all();
        }
        ...
    }

Important issue that if you need to unsubscribe your customer or delete it, customer methods don't help in this case. You need to use Member as an EntityType. Check example below (from CustomerModel ActiveRecord class) for more details:

    public function supportEvent(string $eventType): bool
    {
        $support = YII_ENV == 'prod';

        if ($support && $eventType == ActiveRecord::EVENT_BEFORE_UPDATE && $this->subscribe <> $this->oldAttributes['subscribe']) {
            $support = true;
        }

        return $support;
    }

    public function createAndSaveMailchimpEvent(string $eventType): MailchimpEvent
    {
        if ($this->user->status == BaseActiveRecord::DELETED && $eventType == ActiveRecord::EVENT_AFTER_UPDATE) {
            $event = MailchimpEvent::createEmpty()
                ->setEntityId(MC_PREFIX . $this->id)
                ->setEntityType(MailchimpEvent::CONCACT)
                ->setEventType(ActiveRecord::EVENT_AFTER_DELETE)
                ->setData($this->getMailchimpMemberData())
                ->save();
        } elseif ($eventType == ActiveRecord::EVENT_BEFORE_UPDATE) {
            $event = MailchimpEvent::createEmpty()
                ->setEntityId(MC_PREFIX . $this->id)
                ->setEntityType(MailchimpEvent::CONCACT)
                ->setEventType(ActiveRecord::EVENT_AFTER_UPDATE)
                ->setData($this->getMailchimpMemberData())
                ->save();
        } else {
            $event = MailchimpEvent::createEmpty()
                ->setEntityId(MC_PREFIX . $this->id)
                ->setEntityType(MailchimpEvent::CUSTOMER)
                ->setEventType($eventType)
                ->setData($this->getMailchimpData())
                ->save();
        }

        return $event;
    }

    public function getMailchimpData()
    {
        return [
            'id'            => (string) MC_PREFIX . $this->id,
            'email_address' => (string) $this->email,
            'opt_in_status' => (bool) $this->subscribe,
            'first_name'    => (string) $this->name,
            'orders_count'  => count($this->orders),
            'total_spent'   => (float) OrderModel::find()
                ->where(['transaction.status' => OrderModel::PAYED, 'order.customer_id' => $this->id])
                ->joinWith('transaction')
                ->sum('transaction.total')
        ];
    }

    public function getMailchimpMemberData()
    {
        return [
            'email_address' => $this->user->status == self::DELETED ? $this->name: $this->email,
            'status' => $this->subscribe ? 'subscribed' : 'unsubscribed'
        ];
    }
