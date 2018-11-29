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

    private $logDir = "@runtime/mailchimp_event_pusher/logs";

    public function __construct(string $storeId, MailChimpEventSender $eventSender)
    {
        if (!\Yii::$app->db->getTableSchema($this->mailchimp_events_table)) {
            throw new \Exception('TABLE mailchimp_event DOES`NT EXIST');
        }

        $this->storeId = $storeId;

        $this->eventManager = $eventSender;

        $this->initLogger();

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

    private function initLogger(): void
    {
        $targets = \Yii::$app->getLog()->targets;

        $config = [
            'levels' => ['error', 'warning', 'trace', 'info'],
            'logFile' => \Yii::getAlias($this->logDir). '.log',
            'logVars' => [],
            'except' => [
                'yii\db\*', // Don't include messages from db
            ],
        ];

        $targets['mailchimp'] = new \yii\log\FileTarget($config);
        \Yii::$app->getLog()->targets = $targets;
        \Yii::$app->getLog()->init();
    }

}
