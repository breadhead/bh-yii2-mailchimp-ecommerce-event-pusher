<?php
namespace breadhead\mailchimp;

interface MailchimpEventInterface
{
    public function supportEvent(string $eventType): bool;

    public function createAndSaveMailchimpEvent(string $eventType): MailchimpEvent;
}
