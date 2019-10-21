<?php

class nc_payment_system_paypal extends nc_payment_system {

    const TARGET_URL = "https://www.paypal.com/cgi-bin/webscr";
    const TARGET_SANDBOX_URL = "https://www.sandbox.paypal.com/cgi-bin/webscr";

    protected $automatic = TRUE;

    // принимаемые валюты
    protected $accepted_currencies = array('RUB', 'RUR', 'USD', 'EUR');
    protected $currency_map = array('RUR' => 'RUB');

    // передаваемые параметры
    protected $request_settings = array();

    // параметры сайта в платежной системе
    protected $settings = array(
        'PaypalBizMail' => null,
        'PaymentSuccessPage' => null,
        'PaymentFailedPage' => null,
        'UseSandbox' => 0,
    );

    /**
     * @param nc_payment_invoice $invoice
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        ob_end_clean();
        $nc_core = nc_core::get_object();

        $target_url = $this->get_setting('UseSandbox') ? self::TARGET_SANDBOX_URL : self::TARGET_URL;

        $form = "
            <html>
             <body>
             <form id='fpaypal' action='$target_url' method='post'>" .
            $this->make_inputs(array(
                // В документации PayPal в возможных значениях charset есть 'UTF-8', но нет 'utf-8'.
                // По факту значение в этой форме не влияет на результат, кодировка должна быть настроена в PayPal:
                // Profile → More options → My selling tools → PayPal button language encoding → More options (UTF-8)
                'charset' => $nc_core->NC_UNICODE ? 'UTF-8' : $nc_core->NC_CHARSET,
                'lc' => $nc_core->lang->detect_lang() === 'Russian' ? 'ru_RU' : '',
                'cmd' => '_xclick',
                'business' => $this->get_setting('PaypalBizMail'),
                'item_name' => $invoice->get_description(),
                'item_number' => $invoice->get_id(),
                'amount' => $invoice->get_amount('%0.2F'),
                'currency_code' => $this->get_currency_code($invoice->get_currency()),
                'no_shipping' => 1,
                'rm' => 2, // return method is POST
                'return' => $this->get_setting('PaymentSuccessPage'),
                'cancel_return' => $this->get_setting('PaymentFailedPage'),
                'notify_url' => $this->get_callback_script_url()
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
     *
     */
    public function validate_payment_request_parameters() {
        if (!filter_var($this->get_setting('PaypalBizMail'), FILTER_VALIDATE_EMAIL)) {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_PAYPAL_MAIL_IS_NOT_VALID);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function on_response(nc_payment_invoice $invoice = null) {
        if ($this->get_response_value('payment_status') == 'Completed') {
            $this->on_payment_success($invoice);
        } else {
            $this->on_payment_failure($invoice);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        if (!$invoice) {
            $this->add_error(NETCAT_MODULE_PAYMENT_ERROR_INVOICE_NOT_FOUND);
            return;
        }

        // Проверка сообщения IPN
        if ($this->get_ipn_verification(nc_core::get_object()->input->fetch_post()) !== 'VERIFIED') {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_PAYPAL_IPN_NOT_VERIFIED);
            $invoice->set('status', nc_payment_invoice::STATUS_CALLBACK_ERROR)->save();
            return;
        }

        // Описание данных IPN:
        // https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/

        if ($this->get_response_value('txn_type') !== 'web_accept') {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_SOME_PARAMETERS_ARE_NOT_VALID . ': txn_type');
            $invoice->set('status', nc_payment_invoice::STATUS_CALLBACK_ERROR)->save();
            return;
        }

        // Проверка получателя
        if (strtolower($this->get_response_value('business')) !== strtolower($this->get_setting('PaypalBizMail'))) {
            $this->add_error(NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_PAYPAL_MAIL_IS_NOT_VALID);
            $invoice->set('status', nc_payment_invoice::STATUS_CALLBACK_ERROR)->save();
            return;
        }

        // Проверка суммы
        $invoice_currency = $this->get_currency_code($invoice->get_currency());
        $invoice_amount = $invoice->get_amount('%0.2F');

        $payment_amount = $this->get_response_value('mc_gross');
        $payment_currency = $this->get_response_value('mc_currency');

        if ($invoice_currency != $payment_currency || $invoice_amount != $payment_amount) {
            $this->add_error(NETCAT_MODULE_PAYMENT_ERROR_INVALID_SUM);
            $invoice->set('status', nc_payment_invoice::STATUS_CALLBACK_WRONG_SUM)->save();
        }
    }

    /**
     * @return bool|nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        return $this->load_invoice($this->get_response_value('item_number'));
    }

    /**
     * Проверка сообщения от PayPal(Instant Payment Notifications, IPN)
     * https://developer.paypal.com/docs/classic/ipn/ht_ipn/
     * Возвращает ответ от PayPal
     * @param array $data_to_verify
     * @return string|false
     */
    protected function get_ipn_verification(array $data_to_verify) {
        $curl_options = array(
            CURLOPT_URL => $this->get_setting('UseSandbox') ? self::TARGET_SANDBOX_URL : self::TARGET_URL,
            CURLOPT_POST => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => http_build_query($data_to_verify, null, '&') . '&cmd=_notify-validate', // cmd обязательно должен идти в конце!
            CURLOPT_USERAGENT => 'Netcat CMS', // без User-Agent PayPal отвечает "403 Forbidden"
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            // PayPal начиная с 2016 года требует использовать TLS 1.2.
            // Некоторые необновлённые серверы (декабрь 2016 года) не могут
            // соединиться с серверами PayPal из-за того, что TLS 1.x отключён
            // по умолчанию; явное указание версии может исправить это.
            // Нужно изменить (убрать) при изменении требований PayPal:
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_options);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
