<?

class nc_payment_system_paymaster extends nc_payment_system {

    const TARGET_URL = "https://paymaster.ru/Payment/Init";

    protected $automatic = TRUE;

    // принимаемые валюты
    protected $accepted_currencies = array('RUB', 'RUR', 'USD', 'EUR');
    protected $currency_map = array('RUR' => 'RUB');

    // параметры сайта в платежной системе
    protected $settings = array(
        'LMI_MERCHANT_ID' => null,
        // 'LMI_CURRENCY' => null,
        'SALT' => null, // секретное слово
        'SEND_RECEIPTS' => null,
    );

    // передаваемые параметры
    protected $request_parameters = array(
    );

    // получаемые параметры
    protected $callback_response = array(
        'LMI_PAYMENT_NO' => null,
        'LMI_SYS_PAYMENT_ID' => null,
        'LMI_SYS_PAYMENT_DATE' => null,
        'LMI_PAYMENT_AMOUNT' => null,
        'LMI_PAID_AMOUNT' => null,
        'LMI_PAID_CURRENCY' => null,
        'LMI_PAYMENT_SYSTEM' => null,
        'LMI_SIM_MODE' => null,
    );

    static protected $paymaster_vat_enum = array(
        '' => 'no_vat',
        0 => 'vat0',
        10 => 'vat10',
        18 => 'vat18',
    );
    static protected $paymaster_default_vat = 'vat18';

    /**
     * @param nc_payment_invoice $invoice
     * @return void
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        $inputs = array(
            'LMI_MERCHANT_ID' => $this->get_setting('LMI_MERCHANT_ID'),
            'LMI_PAYMENT_AMOUNT' => $invoice->get_amount(),
            'LMI_CURRENCY' => $this->get_currency_code($invoice->get_currency()),
            'LMI_SIM_MODE' => 0,
            'LMI_PAYMENT_DESC' => $invoice->get_description(),
            'LMI_PAYMENT_NO' => $invoice->get_id(),
            'LMI_PAYER_EMAIL' => $invoice->get('customer_email'),
            'LMI_PAYER_PHONE_NUMBER' => str_replace('+', '', nc_normalize_phone_number($invoice->get('customer_phone'))),
        );

        $i = 0;
        if ($this->get_setting('SEND_RECEIPTS')) {
            $sell_receipt = $invoice->get_sell_receipt();
            if ($sell_receipt) {
                foreach ($sell_receipt->get_items() as $item) {
                    $vat = nc_array_value(self::$paymaster_vat_enum, $item->get('vat_rate'), self::$paymaster_default_vat);
                    $inputs["LMI_SHOPPINGCART.ITEM[$i].QTY"] = $item->get('qty');
                    $inputs["LMI_SHOPPINGCART.ITEM[$i].PRICE"] = sprintf('%0.2F', $item->get('item_price'));
                    $inputs["LMI_SHOPPINGCART.ITEM[$i].TAX"] = $vat;
                    $inputs["LMI_SHOPPINGCART.ITEM[$i].NAME"] = $item->get('name');
                    $i++;
                }
                // нет способа проверить результат? если будет найден, то ↓↓↓ = STATUS_PENDING
                $sell_receipt->save_status($sell_receipt::STATUS_PENDING, array('inputs' => $inputs));
            }
        }

        ob_end_clean();
        $form = "
            <html>
              <body>
                  <form action='" . nc_payment_system_paymaster::TARGET_URL . "' method='post'>
                      {$this->make_inputs($inputs)}
                  </form>
                  <script>
                      document.forms[0].submit();
                  </script>
              </body>
            </html>
            ";
        echo $form;
    }

    /**
     *
     */
    public function validate_payment_request_parameters() {
        if (!$this->get_setting('LMI_MERCHANT_ID')) {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYMASTER_ERROR_MERCHANTID_IS_NOT_VALID);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    protected function validate_invoice(nc_payment_invoice $invoice) {
        parent::validate_invoice($invoice);
        if (strlen($invoice->get_description()) > 255) {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYMASTER_ERROR_LMI_PAYMENT_DESC_IS_LONG);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function on_response(nc_payment_invoice $invoice = null) {
        if ($this->get_response_value('LMI_SYS_PAYMENT_DATE')) {
            $this->on_payment_success($invoice);
        }
        else {
            $this->on_payment_failure($invoice);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        $key = array(
            $this->get_setting('LMI_MERCHANT_ID'),
            $this->get_response_value('LMI_PAYMENT_NO'),
            $this->get_response_value('LMI_SYS_PAYMENT_ID'),
            $this->get_response_value('LMI_SYS_PAYMENT_DATE'),
            $this->get_response_value('LMI_PAYMENT_AMOUNT'),
            $this->get_response_value('LMI_CURRENCY'),
            $this->get_response_value('LMI_PAID_AMOUNT'),
            $this->get_response_value('LMI_PAID_CURRENCY'),
            $this->get_response_value('LMI_PAYMENT_SYSTEM'),
            $this->get_response_value('LMI_SIM_MODE'),
            $this->get_setting('SALT')
        );

        $key = implode(";", $key);
        $key_hash = base64_encode(md5($key, TRUE));

        if ($this->get_response_value('LMI_HASH') != $key_hash) {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYMASTER_ERROR_PRIVATE_SECURITY_IS_NOT_VALID);
        }
    }

    /**
     * @return bool|nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        return $this->load_invoice($this->get_response_value('LMI_PAYMENT_NO'));
    }

    /**
     * @return bool
     */
    public function can_send_receipt_data_with_invoice() {
        return (bool)$this->get_setting('SEND_RECEIPTS');
    }
}
