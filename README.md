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
