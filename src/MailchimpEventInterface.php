<?php
namespace breadhead\mailchimp;

interface MailchimpEventInterface
{
    public function createAndSaveMailchimpEvent(string $eventType): MailchimpEvent;
}
