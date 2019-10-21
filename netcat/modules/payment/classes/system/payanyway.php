<?

class nc_payment_system_payanyway extends nc_payment_system {

    const ERROR_MNT_ID_IS_NOT_VALID = NETCAT_MODULE_PAYMENT_PAYANYWAY_ERROR_MNT_ID_IS_NOT_VALID;

    const TARGET_URL = "https://www.payanyway.ru/assistant.htm";

    protected $automatic = true;

    // принимаемые валюты
    protected $accepted_currencies = array('RUB', 'RUR');
    protected $currency_map = array('RUR' => 'RUB');

    // параметры сайта в платежной системе
    protected $settings = array(
        'MNT_ID' => null,
        'MNT_TEST_MODE' => null,
        'MNT_DATAINTEGRITY_CODE' => null,
        'MNT_KASSA_ENABLED' => null,
    );

    // передаваемые параметры
    protected $request_parameters = array(
    );

    // получаемые параметры
    protected $callback_response = array(
        'MNT_ID' => null,
        'MNT_TRANSACTION_ID' => null,
        'MNT_OPERATION_ID' => null,
        'MNT_TEST_MODE' => null,
        'MNT_AMOUNT' => null,
        'MNT_CURRENCY_CODE' => null,
        'MNT_SIGNATURE' => null,
    );

    static protected $payanyway_vat_enum = array(
        '' => 1105,  // НДС не облагается
        0 => 1104,  // НДС 0%
        10 => 1103,  // НДС 10%
        18 => 1102,  // НДС 18%
    );
    static protected $payanyway_default_vat = 1102;

    /**
     * @param nc_payment_invoice $invoice
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        $amount = $invoice->get_amount('%0.2F');
        $currency = $this->get_currency_code($invoice->get_currency());

        $signature_values = array(
            $this->get_setting('MNT_ID'),
            $invoice->get_id(),
            $amount,
            $currency,
            $this->get_setting('MNT_TEST_MODE'),
            $this->get_setting('MNT_DATAINTEGRITY_CODE'),
        );
        $signature = md5(join('', $signature_values));

        ob_end_clean();
        $form = "
            <html>
              <body>
                  <form action='" . self::TARGET_URL . "' method='post' accept-charset='utf-8'>" .
                  $this->make_inputs(array(
                      'MNT_ID' => $this->get_setting('MNT_ID'),
                      'MNT_TRANSACTION_ID' => $invoice->get_id(),
                      'MNT_AMOUNT' => $amount,
                      'MNT_CURRENCY_CODE' => $currency,
                      'MNT_TEST_MODE' => $this->get_setting('MNT_TEST_MODE'),
                      'MNT_DESCRIPTION' => $invoice->get_description(),
                      'MNT_SIGNATURE' => $signature,
                  )) . "
                  </form>
                  <script>
                      document.forms[0].submit();
                  </script>
              </body>
            </html>";
        echo $form;
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function on_response(nc_payment_invoice $invoice = null) {
        $invoice->set('status', nc_payment_invoice::STATUS_SUCCESS);
        $invoice->save();
        $this->on_payment_success($invoice);
        echo 'SUCCESS';
    }

    /**
     * @param nc_payment_receipt $receipt
     */
    public function process_receipt(nc_payment_receipt $receipt) {
        $invoice = $receipt->get_invoice();

        $nc_core = nc_core::get_object();
        $callback_url = $nc_core->catalogue->get_url_by_id($invoice->get('site_id')) .
                        nc_module_path('payment') . '?paySystem=' . __CLASS__;

        $inventory_positions = array();
        $items = $receipt->get_items();
        foreach ($items as $item) {
            $inventory_positions[] = array(
                'name' => $item->get('name'),
                'price' => (float)$item->get('item_price'),
                'quantity' => (float)$item->get('qty'),
                'vatTag' => nc_array_value(self::$payanyway_vat_enum, $item->get('vat_rate'), self::$payanyway_default_vat),
            );
        }

        $sum = $items->sum('total_price');

        $data = array(
            'id' => $receipt->get_id(),
            'checkoutDateTime' => date(DATE_ATOM),
            'docNum' => $receipt->get_id(),
            'docType' => $receipt->get('operation') === nc_payment::OPERATION_SELL ? 'SALE' : 'REFUND',
            'email' => $invoice->get('customer_email') ?: nc_payment_register::get_default_customer_email(),
            'responseURL' => $callback_url . '&receipt_id=' . $receipt->get_id(),
            'inventPositions' => $inventory_positions,
            'moneyPositions' => array(array('paymentType' => 'CARD', 'sum' => $sum))
        );

        $data['signature'] = md5($data['id'] . $data['checkoutDateTime'] . $this->get_setting('MNT_DATAINTEGRITY_CODE'));
        $json_data = json_encode($data, 256); // JSON_UNESCAPED_UNICODE = 256 (PHP 5.4+)

        $operation_url = 'https://kassa.payanyway.ru/api/api.php?method=sale&accountid=' . $this->get_response_value('MNT_ID');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $operation_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data)
        ));

        $receipt->save_status(nc_payment_receipt::STATUS_PENDING, array(
            'url' => $operation_url,
            'data' => $data
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        $receipt->save_status($receipt::STATUS_REGISTERED, array('result' => $result));
    }

    /**
     *
     */
    public function validate_payment_request_parameters() {
        if (!$this->get_setting('MNT_ID')) {
            $this->add_error(nc_payment_system_payanyway::ERROR_MNT_ID_IS_NOT_VALID);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        $their_key = strtoupper($this->get_response_value('MNT_SIGNATURE'));

        $signature_values = array(
            $this->get_response_value('MNT_ID'),
            $this->get_response_value('MNT_TRANSACTION_ID'),
            $this->get_response_value('MNT_OPERATION_ID'),
            $this->get_response_value('MNT_AMOUNT'),
            $this->get_response_value('MNT_CURRENCY_CODE'),
            $this->get_response_value('MNT_TEST_MODE'),
            $this->get_setting('MNT_DATAINTEGRITY_CODE')
        );
        $our_key = strtoupper(md5(join("", $signature_values)));

        if (!$invoice || $our_key != $their_key) {
            if ($invoice) {
                $invoice->set('status', nc_payment_invoice::STATUS_CALLBACK_ERROR);
                $invoice->save();
            }
            die('FAIL');
        }
    }

    /**
     * @return bool|nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        return $this->load_invoice($this->get_response_value('MNT_TRANSACTION_ID'));
    }

    /**
     * @return bool
     */
    public function can_send_custom_receipts() {
        return (bool)$this->get_setting('MNT_KASSA_ENABLED');
    }

}
