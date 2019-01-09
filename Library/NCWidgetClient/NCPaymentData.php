<?php

namespace NetCents\Merchant\Library\NCWidgetClient;

class NCPaymentData {

    public $widgetId;
    public $externalId;
    public $amount;
    public $callbackUrl;
    public $firstName;
    public $lastName;
    public $email;
    public $webhookUrl;
    public $apiKey;
    public $secretKey;
    public $merchantUrl;
    public $currencyIso;

    function __construct(
        $widgetId,
        $externalId,
        $amount,
        $callbackUrl,
        $firstName,
        $lastName,
        $email,
        $webhookUrl,
        $apiKey,
        $secretKey,
        $merchantUrl,
        $currencyIso
    ) {
        $this->widgetId = $widgetId;
        $this->externalId = $externalId;
        $this->amount = $amount;
        $this->callbackUrl = $callbackUrl;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->webhookUrl = $webhookUrl;
        $this->apiKey = $apiKey;
        $this->currencyIso = $currencyIso;
        $this->secretKey =  $secretKey;
        $this->merchantUrl = $merchantUrl;
    }
}


