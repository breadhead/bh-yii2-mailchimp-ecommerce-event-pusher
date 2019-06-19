<?php
namespace breadhead\mailchimp;

use yii\base\Event;
use yii\db\ActiveRecord;

class MailchimpEventPusher
{
    private $eventManager;

    public $storeId;

    private $events = [
        ActiveRecord::EVENT_AFTER_INSERT,
        ActiveRecord::EVENT_AFTER_UPDATE,
        ActiveRecord::EVENT_AFTER_DELETE
    ];

    /* Don`t forget to do migration */
    private $mailchimp_events_table = 'mailchimp_event';

    public function __construct(string $storeId, MailChimpEventSender $eventSender)
    {
        if (!\Yii::$app->db->getTableSchema($this->mailchimp_events_table)) {
            throw new \Exception('TABLE mailchimp_event DOES`NT EXIST');
        }

        $this->storeId = $storeId;

        $this->eventManager = $eventSender;

        $this->init();
    }

    public function getManager()
    {
        return $this->eventManager;
    }

    private function init(): void
    {
        $this->setEvents();
    }

    private function setEvents()
    {
        foreach ($this->events as $eventName) {
            Event::on(MailchimpEventInterface::class, $eventName, function ($event) {
                if( $event->sender->supportEvent($event->name)) {
                    $event->sender->createAndSaveMailchimpEvent($event->name);
                };
            });
        }

        return $this;
    }
}
