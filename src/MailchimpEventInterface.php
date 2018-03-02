<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 15.12.2017
 * Time: 17:54
 */

namespace bh\mailchimp;

/**
 * Interface MailchimpEventInterface
 * @package MailchimpEventPusher
 */
interface MailchimpEventInterface
{
    /**
     * getMailchimpEvent
     * @param string $event_type
     * @return MailchimpEvent
     */
    public function saveMailchimpEvent(string $event_type): MailchimpEvent;
}