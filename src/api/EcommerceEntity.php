<?php
namespace breadhead\mailchimp\api;

abstract class EcommerceEntity
{
    protected $client;

    public function __construct(MailchimpClient $client)
    {
        $this->client = $client;
    }
}
