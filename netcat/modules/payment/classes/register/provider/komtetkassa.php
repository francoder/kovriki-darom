<?php

/**
 * Класс для интеграции с сервисом онлайн-касс КОМТЕТ Касса
 */
class nc_payment_register_provider_komtetkassa extends nc_payment_register_provider {

    const API_URL = 'https://kassa.komtet.ru/api/shop/v1/';

    static protected $settings = array(
        'PaymentRegisterKomtetKassaShopId' => NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_SHOP_ID,
        'PaymentRegisterKomtetKassaShopSecret' => NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_SHOP_SECRET,
        'PaymentRegisterKomtetKassaQueueID' => NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_QUEUE_ID,
        'PaymentRegisterKomtetKassaPrintCheck' => NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_PRINT_CHECK,
    );

    static protected $komtetkassa_vat_enum = array(
        '' => 'no',
        0  => '0',
        10 => '10',
        18 => '18',
    );
    static protected $komtetkassa_default_vat = '18';

    static protected $komtetkassa_tax_enum = array(
        'osn'                => 0,
        'usn_income'         => 1,
        'usn_income_outcome' => 2,
        'envd'               => 3,
        'esn'                => 4,
        'patent'             => 5
    );

    /**
     * Обработка нового чека
     *
     * @param nc_payment_receipt $receipt
     */
    public function process_receipt(nc_payment_receipt $receipt) {
        $nc_core = nc_core::get_object();
        $invoice = $receipt->get_invoice();

        $tax_variant  = self::$komtetkassa_tax_enum[$this->get_setting('PaymentRegisterSN')];
        $intent = $receipt->get('operation') === nc_payment::OPERATION_SELL ? 'sell' : 'sellReturn';
        $receipt_id = (string)$receipt->get_id();
        $items = $receipt->get_items();
        $totals = (float)$items->sum('total_price');

        $data = array(
            'task_id' => $receipt_id,
            'user' => $invoice->get_customer_contact_for_receipt(),
            'print' => in_array($this->get_setting('PaymentRegisterKomtetKassaPrintCheck'), array('Да', 'Yes', '1')) ? true : false,
            'intent' => $intent,
            'sno' => $tax_variant,
            'payments' => array(
                array(
                    'type' => 'card',
                    'sum' => $totals
                )
            ),
            'positions' => array()
        );

        // Список товаров
        /** @var nc_payment_invoice_item $item */
        foreach ($items as $item) {
            $data['positions'][] = array(
                'name' => nc_substr($item->get('name'), 0, 128), // Максимальная длина строки – 128 символов,
                'price' => (float)$item->get('item_price'),
                'quantity' => (float)$item->get('qty'),
                'total' => (float)$item->get('total_price'),
                'vat' => nc_array_value(self::$komtetkassa_vat_enum, $item->get('vat_rate'), self::$komtetkassa_default_vat)
            );
        }

        $path = sprintf('queues/%s/task', $this->get_setting('PaymentRegisterKomtetKassaQueueID'));
        $response = $this->execute_request($path, $data);

        $receipt->set('transaction_id', nc_array_value($response, 'id'));
        $receipt->save_status(
            (!empty($response['error'])) ? $receipt::STATUS_FAILED : $receipt::STATUS_PENDING,
            array(
                'path' => $path,
                'data' => $data,
                'response' => $response,
            )
        );
    }

    /**
     * Обработка колбека от кассового сервиса
     */
    public function process_callback() {
        $callback_data = json_decode(file_get_contents('php://input'), true);
        $task_id = $callback_data['id'];

        $path = sprintf('tasks/%s', $task_id);
        $response = $this->execute_request($path);

        $receipt_id = $response['external_id'];
        $status = $response['state'];
        $receipt = new nc_payment_receipt($receipt_id);

        if ($status === 'done') {
            if (isset($response['fiscal_data'])) {
                $fd = $response['fiscal_data'];
                $receipt_datetime = DateTime::createFromFormat('Ymd\THi', $fd['t'])->format("Y-m-d H:i");

                $receipt->set_values(array(
                    'amount' => $fd['s'],
                    'fiscal_document_attribute' => $fd['fp'],
                    'fiscal_document_number' => $fd['i'],
                    'fiscal_receipt_created' => $receipt_datetime,
                    'fiscal_receipt_number' => $fd['shn'],
                    'fiscal_storage_number' => $fd['fn'],
                    'shift_number' => $fd['sh'],
                    'transaction_id' => $response['id']
                ));
            }

            $receipt->save_status(
                $receipt::STATUS_REGISTERED,
                array(
                    'fiscal_data' => $response
                )
            );
        } else if ($status === 'error') {
            $receipt->save_status(
                $receipt::STATUS_FAILED,
                array(
                    'fiscal_data' => $response
                )
            );
        }
    }

    /**
     * Функция для создания CURL-запроса
     *
     * @param $path
     * @param null|array $data
     * @return array
     */
    protected function execute_request($path, array $data = null) {
        if ($data === null) {
            $method = 'GET';
        } elseif (is_array($data)) {
            $method = 'POST';
            $data = nc_array_json($data);
        } else {
            throw new InvalidArgumentException('Unexpected type of $data, accepts array or null');
        }

        $url = self::API_URL . $path;
        $signature = hash_hmac(
            'md5',
            $method . $url . ($data ? $data : ''),
            $this->get_setting('PaymentRegisterKomtetKassaShopSecret')
        );

        $headers = array(
            'Accept: application/json',
            sprintf('Authorization: %s', $this->get_setting('PaymentRegisterKomtetKassaShopId')),
            sprintf('X-HMAC-Signature: %s', $signature)
        );

        if ($method == 'POST') {
            $headers[] = 'Content-Type: application/json';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (!json_last_error()) {
                if ($httpcode === 200) {
                    return $result;
                } else {
                    return array('error' => array('text' => 'API request error'), 'response' => $response);
                }
            } else {
                return array('error' => array('text' => 'JSON error: ' . json_last_error_msg()), 'response' => $response);
            }
        } else if ($curl_error) {
            return array('error' => array('text' => $curl_error), 'connection_error' => true);
        } else {
            return array('error' => array('text' => 'empty response'));
        }
    }
}