<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 15.12.2017
 * Time: 17:48
 */

namespace bh\mailchimp;

use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Class MailchimpEventPusher
 * @package MailchimpEventPusher
 */
class MailchimpEventPusher
{
    private $event_manager;

    public $store_id;

    private $events = [
        ActiveRecord::EVENT_AFTER_INSERT,
        ActiveRecord::EVENT_AFTER_UPDATE,
        ActiveRecord::EVENT_AFTER_DELETE
    ];

    /* Don`t forget to do migration */
    private $mailchimp_events_table = 'mailchimp_event';

    private $logDir = "@runtime/mailchimp_event_pusher/logs";


    /**
     * MailchimpEventPusher constructor.
     * @param $store_id
     * @throws \Exception
     */
    public function __construct($store_id)
    {
        if (!\Yii::$app->db->getTableSchema($this->mailchimp_events_table)) {
            throw new \Exception('TABLE mailchimp_event DOES`NT EXIST');
        }

        $this->store_id = $store_id;

        $this->event_manager = new MailChimpEventSender($this->store_id);

        $this->initLogger()->init();
    }

    /**
     * @return MailChimpEventSender
     */
    public function getManager()
    {
        return $this->event_manager;
    }

    /**
     * init
     */
    private function init()
    {
        $this->setEvents();

        return $this;
    }

    /**
     * getEvents
     */
    private function setEvents()
    {
        foreach ($this->events as $trigger) {
            Event::on(MailchimpEventInterface::class, $trigger, function ($event) {
                $event->sender->saveMailchimpEvent($event->name);
            });
        }

        return $this;
    }

    /**
     * init logger to defined directory
     *
     * @return $this
     */
    private function initLogger()
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

        return $this;
    }

}
