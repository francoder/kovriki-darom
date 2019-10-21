<?php

class nc_payment_register_provider_cloudkassir extends nc_payment_register_provider {

    static protected $settings = array(
        'PaymentRegisterCloudKassirPublicId'    => NETCAT_MODULE_PAYMENT_REGISTER_CLOUDKASSIR_PUBLIC_ID,
        'PaymentRegisterCloudKassirSecretKey'   => NETCAT_MODULE_PAYMENT_REGISTER_CLOUDKASSIR_SECRET_KEY,
    );

    static protected $cloudkassir_vat_enum = array(
        ''   => '',
        0    => '0',
        10   => '10',
        18   => '18',
    );
    static protected $cloudkassir_default_vat = '18';

    /**
     * Обработка нового чека (отправка запроса на создание чека в ККМ)
     *
     * @param nc_payment_receipt $receipt
     */
    public function process_receipt(nc_payment_receipt $receipt) {
        $invoice = $receipt->get_invoice();
        $tax_array    = array(
            'osn'                => 0,
            'usn_income'         => 1,
            'usn_income_outcome' => 2,
            'envd'               => 3,
            'esn'                => 4,
            'patent'             => 5
        );
        $tax_variant  = $tax_array[$this->get_setting('PaymentRegisterSN')];
        $receipt_type = $receipt->get('operation') === nc_payment::OPERATION_SELL ? 'Income' : 'IncomeReturn';

        $data = array(
            'Inn'             => $this->get_setting('PaymentRegisterINN'),
            'Type'            => $receipt_type,
            'CustomerReceipt' => array(
                'Items'          => array(),
                'taxationSystem' => $tax_variant,
                'email'          => $invoice->get('customer_email'),
                'phone'          => $invoice->get('customer_phone')
            ),
            'InvoiceId'       => $invoice->get('order_id'),
            'AccountId'       => $invoice->get('customer_email'),
        );

        $items = $receipt->get_items();
        foreach ($items as $item) {
            $data['CustomerReceipt']['Items'][] = array(
                'label'    => $item->get('name'),
                'price'    => sprintf('%0.2F', $item->get('item_price')),
                'quantity' => intval($item->get('qty')),
                'amount'   => sprintf('%0.2F', $item->get('total_price')),
                'vat'      => nc_array_value(self::$cloudkassir_vat_enum, $item->get('vat_rate'), self::$cloudkassir_default_vat)
            );
        }
        $response = $this->execute_request('kkt/receipt', $data);
        if (isset($response['Error'])) {
            $receipt_status = !empty($result['ConnectionError']) ? $receipt::STATUS_CONNECTION_ERROR : $receipt::STATUS_FAILED;
        } else {
            $receipt_status = $receipt::STATUS_REGISTERED;
        }
        $receipt->save_status(
            $receipt_status,
            array(
                'data'     => $data,
                'response' => $response,
            )
        );
    }

    private function execute_request($location, $data = array()) {
        $ch = curl_init('https://api.cloudpayments.ru/' . $location);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->get_setting(
                'PaymentRegisterCloudKassirPublicId') . ':' . $this->get_setting('PaymentRegisterCloudKassirSecretKey')
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "content-type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, nc_array_json($data));

        $response   = curl_exec($ch);
        $curl_error = curl_error($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (!json_last_error()) {
                if (!nc_array_value($result, 'Success')) {
                    return array('Error' => 'API request error', 'Response' => $result);
                } else {
                    return $result;
                }
            } else {
                return array('Error' => 'JSON error: ' . json_last_error_msg(), 'Response' => $response);
            }
        } else if ($curl_error) {
            return array('Error' => $curl_error, 'ConnectionError' => true);
        } else {
            return array('Error' => '(empty response)', 'ConnectionError' => true);
        }
    }
}
