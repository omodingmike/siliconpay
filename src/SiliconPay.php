<?php

    namespace Omodingmike\Siliconpay;

    use GuzzleHttp\Client;

    class SiliconPay
    {
        protected string $api_key;
        protected string $callback_url;
        protected string $txRef;
        protected string $payment_url;

        /**
         * @param string $api_key
         * @param string $callback_url
         * @param string $txRef
         * @param string $payment_url
         */
        public function __construct(string $api_key, string $callback_url, string $txRef, string $payment_url)
        {
            $this->api_key      = $api_key;
            $this->callback_url = $callback_url;
            $this->txRef        = $txRef;
            $this->payment_url  = $payment_url;
        }

        /**
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function pay($amount, $phone, $email)

        {
            $payload = [
                'req'            => 'mobile_money',
                'currency'       => 'UGX',
                'phone'          => $phone,
                'encryption_key' => $this->api_key,
                'amount'         => $amount,
                'emailAddress'   => $email,
                'call_back'      => $this->callback_url,
                'txRef'          => $this->txRef
            ];
            $client  = new Client();
            return $client->request('POST', $this->payment_url, $payload);
        }

        public function isCallbackValid()
        : bool
        {
            $body        = file_get_contents('php://input');
            $dataObject  = json_decode($body);
            $reference   = $dataObject->txRef;
            $secure_hash = $dataObject->secure_hash;
            $secrete_key = $this->api_key;
            // Generate a secure hash on your end.
            $cipher         = 'aes-256-ecb';
            $generated_hash = openssl_encrypt($reference, $cipher, $secrete_key);
            // authenticating the callback data
            return $generated_hash == $secure_hash;
        }
    }