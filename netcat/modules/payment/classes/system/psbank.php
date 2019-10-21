<?

/**
 * Класс для интеграции с платёжным шлюзом Промсвязьбанка.
 * Все права на программный код в данном файле принадлежат ПАО «Промсвязьбанк».
 */

class nc_payment_system_psbank extends nc_payment_system {

    const TARGET_URL = 'https://3ds.payment.ru/cgi-bin/cgi_link';
    const TARGET_TEST_URL = 'https://test.3ds.payment.ru/cgi-bin/cgi_link';

    const TRANSACTION_TYPE_PAYMENT = 1;
    // не используется, пока в модуле нет автоматического возврата средств:
    // const TRANSACTION_TYPE_REFUND = 14;

    // направление данных (запрос к шлюзу или ответ от него) — для вычисления подписи
    const DATA_TO_GATEWAY = 'request';
    const DATA_FROM_GATEWAY = 'response';

    // значения RESULT в ответах
    const RESULT_SUCCESS = '0';
    const RESULT_DUPLICATE = '1';

    protected $automatic = true;

    // принимаемые валюты
    protected $accepted_currencies = array('RUB', 'RUR');
    protected $currency_map = array('RUR' => 'RUB');

    // параметры сайта в платежной системе
    protected $settings = array(
        'TERMINAL' => null,
        'MERCHANT' => null,
        'KEY' => null,
        'MERCH_NAME' => null,
        'EMAIL' => null,
        'BACKREF' => null,
        'TEST_MODE' => null,
    );

    // элементы, используемые для вычисления HMAC (параметр P_SIGN)
    protected $signature_parts = array(
        self::TRANSACTION_TYPE_PAYMENT => array(
            self::DATA_TO_GATEWAY => array(
                'AMOUNT', 'CURRENCY', 'ORDER', 'MERCH_NAME', 'MERCHANT', 'TERMINAL',
                'EMAIL', 'TRTYPE', 'TIMESTAMP', 'NONCE', 'BACKREF',
            ),
            self::DATA_FROM_GATEWAY => array(
                'AMOUNT', 'CURRENCY', 'ORDER', 'MERCH_NAME', 'MERCHANT', 'TERMINAL',
                'EMAIL', 'TRTYPE', 'TIMESTAMP', 'NONCE', 'BACKREF',
                'RESULT', 'RC', 'RCTEXT', 'AUTHCODE', 'RRN', 'INT_REF',
            ),
        ),
    );

    /**
     * @param nc_payment_invoice $invoice
     * @return void
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        $inputs = array(
            'AMOUNT' => $invoice->get_amount('%0.2F'),
            'CURRENCY' => $this->get_currency_code($invoice->get_currency()),
            'ORDER' => $this->get_external_order_id($invoice->get_id()),
            'DESC' => nc_substr($invoice->get_description(), 0, 50),
            'TERMINAL' => $this->get_setting('TERMINAL'),
            'TRTYPE' => self::TRANSACTION_TYPE_PAYMENT,
            'MERCH_NAME' => nc_substr($this->get_setting('MERCH_NAME'), 0, 22),
            'MERCHANT' => $this->get_setting('MERCHANT'),
            'EMAIL' => $this->get_setting('EMAIL'),
            'TIMESTAMP' => gmdate('YmdHis'),
            'NONCE' => strtoupper(md5(mt_rand() . microtime())),
            'BACKREF' => $this->get_setting('BACKREF'),
        );

        $nc_core = nc_core::get_object();
        if (!$nc_core->NC_UNICODE) {
            $inputs = $nc_core->utf8->array_win2utf($nc_core);
        }

        $inputs['P_SIGN'] = $this->calculate_signature($inputs, self::DATA_TO_GATEWAY);

        $url = $this->get_setting('TEST_MODE') ? self::TARGET_TEST_URL : self::TARGET_URL;

        ob_end_clean();
        $form = "
            <html>
              <body>
                  <form action='$url' method='post'>
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
        $required_settings = array('TERMINAL', 'MERCHANT', 'KEY');
        foreach ($required_settings as $key) {
            if (!$this->get_setting($key)) {
                $this->add_error(sprintf(NETCAT_MODULE_PAYMENT_SETTING_MISSING_VALUE, $key));
            }
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function on_response(nc_payment_invoice $invoice = null) {
        $result_code = $this->get_response_value('RESULT');
        if ($result_code == self::RESULT_SUCCESS) {
            $this->on_payment_success($invoice);
        } else if ($result_code != self::RESULT_DUPLICATE) {
            $this->on_payment_failure($invoice);
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        // проверка подписи
        $hmac = $this->calculate_signature($this->get_response(), self::DATA_FROM_GATEWAY);
        if ($hmac !== $this->get_response_value('P_SIGN')) {
            $this->add_error(NETCAT_MODULE_PAYMENT_PSBANK_ERROR_INVALID_HMAC);
        }

        if (!$invoice) {
            // ошибка «счёт не найден» уже добавлена в nc_payment_system::process_callback_response()
            return;
        }

        // проверка суммы платежа
        $amount_is_incorrect =
            $invoice->get_amount('%0.2F') != $this->get_response_value('AMOUNT')  ||
            $this->get_currency_code($invoice->get_currency()) !== $this->get_response_value('CURRENCY');

        if ($amount_is_incorrect) {
            $this->add_error(NETCAT_MODULE_PAYMENT_ERROR_INVALID_SUM);
        }
    }

    /**
     * @return bool|nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        $invoice_id = $this->get_internal_invoice_id($this->get_response_value('ORDER'));
        return $this->load_invoice($invoice_id);
    }

    /**
     * Вычисление HMAC для P_SIGN
     * @param array $data
     * @param string $direction константа self::DATA_*
     * @return string|null
     */
    protected function calculate_signature(array $data, $direction) {
        if (!isset($data['TRTYPE']) || !isset($this->signature_parts[$data['TRTYPE']][$direction])) {
            trigger_error(__METHOD__ . ': unknown TRTYPE or $direction', E_USER_WARNING);
            return null;
        }

        $parts = $this->signature_parts[$data['TRTYPE']][$direction];
        $mac_data = '';

        foreach ($parts as $key) {
            $value = nc_array_value($data, $key);
            $value_length = strlen($value);
            $mac_data .= $value_length === 0 ? '-' : $value_length . $value;
        }

        $hmac = hash_hmac('sha1', $mac_data, pack('H*', $this->get_setting('KEY')));
        return strtoupper($hmac);
    }

    /**
     * Получение значения ORDER из ID счёта
     * @param $order_id
     * @return string
     */
    protected function get_external_order_id($order_id) {
        // номер заказа в запросе может быть не короче 6 цифр, поэтому добавляем к ID счёта дату
        return sprintf('%d%010d', date('Ymd'), $order_id);
    }

    /**
     * Получение ID счёта из параметра ORDER
     * @param $external_order_id
     * @return int
     */
    protected function get_internal_invoice_id($external_order_id) {
        return (int)substr($external_order_id, 8);
    }

}
