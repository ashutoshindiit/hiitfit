<?php
if (!defined('ABSPATH')) {
    die('You are not allowed to call this page directly.');
}

class MeprMyFatoorahAPI
{
    public $plugin_name;
    protected $apiKey;
    protected $apiUrl;
    protected $paymentMethod;

    public function __construct($settings)
    {
        $this->plugin_name = 'myfatoorah-memberpress';
        $this->paymentMethod = isset($settings->paymentMethod) ? $settings->paymentMethod : 'VISA/MASTER';
        $this->apiKey = isset($settings->apiKey) ? $settings->apiKey : 'TNdij9f4Jp9MjaNp2d1h1jzIDyOQuwBJlVACx9z5wPnUcUj5CGgX2frwuIsb6gjblbAvAfNAd4caPtgDFnV_Oqrh5EchjresP8XNndPi2NMTaabrloO5ZPdiTvuxIZlYCZrj3qSBPJhS-bO8Objo45aIzVDZqVhZJZ4l73AuFI8CfTbbp1xn48nJoTQ3PTHbQ8gJZHmm2LekssmKmoRZywYRyPgveFR0m--bq6fBOxx3Qwkgv-49WcpCyhjknV-tKY-VXmHU5CettrnDAkpcVgEN7TROI9YlQKIrbo5uMsqywmAnYXNHBhiPPBZxyjn9LikmCBpGyno0Vi6NqQMHtik27BGd1eXiPddodX8wzAzqay06UqJHs6AU1bicQ-uX7ZHu2jp5UxE6AjA3wE-NFlXKCaq5mKifVpxLGw7TVMaddGVN8FV_0aIMrgXLqmLJfV8powevwcon3THvoetgJ2kDl84dapwxa4yZ5gmY_8AbBSx65fW45ZtIXOX1uJTOPOiK8Uu1TiGOvE5op52qAfH8n3FgVN90yNPiAvROA3fgVl8R-Pf-4eE-7gX-F3dcsHS2vnDjFAbTqxH3PUYVg_xfcXKrKCjaFZ7Nw0BCstlmNz4vZ1tHvPM5LYey8qERQeW0NSnSsZjETKKowufgnGyijTUrXhNJMtDnfvRZOOEO9PYLNzPwVZU3DQqkuyfrEYJ0xA';
        $this->apiUrl = isset($settings->apiUrl) ? $settings->apiUrl : 'https://apitest.myfatoorah.com';
    }

    public function getPaymentId($ipPostFields)
    {
        $paymentMethods = $this->initiatePayment($ipPostFields);

        $paymentMethodId = null;
        foreach ($paymentMethods as $pm) {
            if ($pm->PaymentMethodEn == $this->paymentMethod) {
                $paymentMethodId = $pm->PaymentMethodId;
                break;
            }
        }

        return $paymentMethodId;
    }

    /**
     * Initiate Payment Endpoint Function 
     */

    public function initiatePayment($postFields)
    {
        $json = $this->callAPI("$this->apiUrl/v2/InitiatePayment", $this->apiKey, $postFields);
        return $json->Data->PaymentMethods;
    }

    /**
     * Execute Payment Endpoint Function 
     */

    public function executePayment($postFields)
    {
        // echo json_encode($postFields);
        // die();
        $json = $this->callAPI("$this->apiUrl/v2/ExecutePayment", $this->apiKey, $postFields);
        return $json->Data;
    }

    /**
     * Direct payment for subscription
     */
    function directPayment($paymentURL, $postFields) {
        // echo json_encode($postFields);
        // die();
        $json = $this->callAPI($paymentURL, $this->apiKey, $postFields);
        return $json->Data;
    }

    /**
     * Cancel recurring payment
     */
    public function cancelSubscription($recurringId)
    {
        $json = $this->callAPI("$this->apiUrl/v2/CancelRecurringPayment?recurringId=" . $recurringId, $this->apiKey, ['recurringId' => $recurringId]);
        return $json->Data;
    }

    /**
     * Call API Endpoint Function
     */

    private function callAPI($endpointURL, $apiKey, $postFields = [], $requestType = 'POST')
    {

        $curl = curl_init($endpointURL);
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => $requestType,
            CURLOPT_POSTFIELDS     => json_encode($postFields),
            CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $response = curl_exec($curl);
        $curlErr  = curl_error($curl);

        curl_close($curl);

        if ($curlErr) {
            //Curl is not working in your server
            die("Curl Error: $curlErr");
        }

        $error = $this->handleError($response);
        if ($error) {
            die("Error: $error");
        }

        return json_decode($response);
    }

    /**
     * Handle Endpoint Errors Function 
     */

    private function handleError($response)
    {

        $json = json_decode($response);
        if (isset($json->IsSuccess) && $json->IsSuccess == true) {
            return null;
        }

        //Check for the errors
        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');

            $error = implode(', ', array_map(function ($k, $v) {
                return "$k: $v";
            }, array_keys($blogDatas), array_values($blogDatas)));
        } else if (isset($json->Data->ErrorMessage)) {
            $error = $json->Data->ErrorMessage;
        }

        if (empty($error)) {
            $error = (isset($json->Message)) ? $json->Message : (!empty($response) ? $response : 'API key or API URL is not correct');
        }

        return $error;
    }
}
