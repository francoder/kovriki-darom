<?php

class nc_payment_system_webmoney extends nc_payment_system {

    const TARGET_URL = "https://merchant.webmoney.ru/lmi/payment.asp";

    protected $automatic = true;

    // принимаемые валюты
    protected $accepted_currencies = array('RUB', 'RUR');

    // параметры сайта в платежной системе
    protected $settings = array(
        'LMI_PAYEE_PURSE' => null,
        'WebmoneySecretKey' => null,
        'WebmoneySecretKeyX20' => null,
    );

    // передаваемые параметры
    protected $request_parameters = array();

    /**
     * @param nc_payment_invoice $invoice
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        $nc_core = nc_core::get_object();

        $amount = $invoice->get_amount('%0.2F');
        $description = $invoice->get_description();
        if (!$nc_core->NC_UNICODE) {
            $description = $nc_core->utf8->win2utf($description);
        }

        if ($this->get_setting('WebmoneySecretKeyX20')) {
            $signature_values = array(
                $this->get_setting('LMI_PAYEE_PURSE'),
                $amount,
                $invoice->get_id(),
                $this->get_setting('WebmoneySecretKeyX20'),
                ''
            );
            $signature = hash('sha256', join(';', $signature_values));
        } else {
            $signature = null;
        }

        ob_end_clean();
        $form = "
            <html>
              <body>
                    <form action='" . nc_payment_system_webmoney::TARGET_URL . "' method='post'>" .
                        $this->make_inputs(array(
                            'LMI_PAYEE_PURSE' => $this->get_setting('LMI_PAYEE_PURSE'),
                            'LMI_PAYMENT_AMOUNT' => $amount,
                            // сейчас поддерживаются только платежи в рублях
                            'LMI_PAYMENT_NO' => $invoice->get_id(),
                            'LMI_PAYMENT_DESC_BASE64' => base64_encode($description),
                            'LMI_SIM_MODE' => 0,
                            'LMI_PAYMENTFORM_SIGN' => $signature,
                        )) . "
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
     * @param nc_payment_invoice $invoice
     */
    public function on_response(nc_payment_invoice $invoice = null) {
        if ($this->get_response_value('LMI_SYS_TRANS_DATE')) {
            $this->on_payment_success($invoice);
        }
        else {
            $this->on_payment_failure($invoice);
        }
    }

    /**
     *
     */
    public function validate_payment_request_parameters() {
        if (!preg_match("/^[ZREUD]\d{12}$/", $this->get_setting('LMI_PAYEE_PURSE'))) {
            $this->add_error(NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_PURSE_IS_NOT_VALID);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    protected function validate_invoice(nc_payment_invoice $invoice) {
        parent::validate_invoice($invoice);
        if (nc_strlen($invoice->get_description()) > 255) {
            $this->add_error(NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_DESCRIPTION_IS_LONG);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        // предварительная проверка со стороны платежной системы перед получением средств
        if ($this->get_response_value('LMI_PREREQUEST') == '1') {
            $this->process_prerequest($invoice);
        }

        $hash_values = array(
            $this->get_setting('LMI_PAYEE_PURSE'),
            $this->get_response_value('LMI_PAYMENT_AMOUNT'),
            $this->get_response_value('LMI_PAYMENT_NO'),
            $this->get_response_value('LMI_MODE'),
            $this->get_response_value('LMI_SYS_INVS_NO'),
            $this->get_response_value('LMI_SYS_TRANS_NO'),
            $this->get_response_value('LMI_SYS_TRANS_DATE'),
            $this->get_setting('WebmoneySecretKey'),
            $this->get_response_value('LMI_PAYER_PURSE'),
            $this->get_response_value('LMI_PAYER_WM')
        );

        $this->check_key($hash_values, $this->get_response_value('LMI_HASH'));

        if (!$invoice) {
            $this->add_error(NETCAT_MODULE_PAYMENT_CANNOT_LOAD_INVOICE_ON_CALLBACK);
        } else if ($invoice->get_amount('%0.2F') != $this->get_response_value('LMI_PAYMENT_AMOUNT')) {
            $this->add_error(NETCAT_MODULE_PAYMENT_ERROR_INVALID_SUM);
        }
    }


    /**
     * @param array $hash_values
     * @param string $expected_key_value
     * @param string $hash_string_separator
     * @return bool
     */
    protected function check_key(array $hash_values, $expected_key_value, $hash_string_separator = '') {
        switch (strlen($expected_key_value)) {
            case 32:
                $hash_algorithm = 'md5';
                break;
            case 64:
                $hash_algorithm = 'sha256';
                break;
            default:
                $this->add_error(NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_UNSUPPORTED_HASH_METHOD);
                return false;
        }

        $hash_string = join($hash_string_separator, $hash_values);
        $our_key = strtoupper(hash($hash_algorithm, $hash_string));

        if ($our_key !== $expected_key_value) {
            $this->add_error(NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_PRIVATE_SECURITY_IS_NOT_VALID);
            return false;
        }

        return true;
    }

    /**
     * @param nc_payment_invoice|null $invoice
     */
    public function process_prerequest(nc_payment_invoice $invoice = null) {
        if (!$invoice) {
            die('ERROR: wrong invoice ID');
        }

        $this->check_prerequest_response('LMI_PAYEE_PURSE', $this->get_setting('LMI_PAYEE_PURSE'));
        $this->check_prerequest_response('LMI_PAYMENT_AMOUNT', $invoice->get_amount('%0.2F'));

        die('YES');
    }

    /**
     * @param $response_value_key
     * @param $expected_value
     */
    protected function check_prerequest_response($response_value_key, $expected_value) {
        if ($this->get_response_value($response_value_key) != $expected_value) {
            die('ERROR: ' . $response_value_key);
        }
    }

    /**
     * @return bool|nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        return $this->load_invoice($this->get_response_value('LMI_PAYMENT_NO'));
    }

}