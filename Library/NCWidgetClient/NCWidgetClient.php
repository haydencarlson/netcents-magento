<?php

namespace NetCents\Merchant\Library\NCWidgetClient;

use NetCents\Merchant\Library\NCPaymentData;

include_once('httpful.phar');

class NCWidgetClient {
    private $paymentData;

    function __construct($paymentData) {
        $this->paymentData = $paymentData;
    }

    function encryptData() {
        $payload = array(
            'external_id' => $this->paymentData->externalId,
            'amount' => $this->paymentData->amount,
            'currency_iso' => $this->paymentData->currencyIso,
            'callback_url' => $this->paymentData->callbackUrl,
            'first_name' => $this->paymentData->firstName,
            'last_name' => $this->paymentData->lastName,
            'email' => $this->paymentData->email,
            'webhook_url' => $this->paymentData->webhookUrl,
            'merchant_id' => $this->paymentData->apiKey,
            'data_encryption' => array(
                'external_id' => $this->paymentData->externalId,
                'amount' => $this->paymentData->amount,
                'currency_iso' => $this->paymentData->currencyIso,
                'callback_url' => $this->paymentData->callbackUrl,
                'first_name' => $this->paymentData->firstName,
                'last_name' => $this->paymentData->lastName,
                'webhook_url' => $this->paymentData->webhookUrl,
                'email' => $this->paymentData->email,
                'merchant_id' => $this->paymentData->apiKey
            ),
        );
        $formHandler =  new \Httpful\Handlers\FormHandler();
        $data = $formHandler->serialize($payload);

        $response =  \Httpful\Request::post($this->paymentData->merchantUrl . '/api/v1/widget/encrypt')
                        ->body($data)
                        ->addHeader('Authorization', 'Basic ' .  base64_encode( $this->paymentData->apiKey. ':' . $this->paymentData->secretKey))
                        ->send();
        return $response;
    }
}