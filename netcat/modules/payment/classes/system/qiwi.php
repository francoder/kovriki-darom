<?php

class nc_payment_system_qiwi extends nc_payment_system {

    const PHONE_REGEXP = '/^\+\d{1,15}$/';

    protected $accepted_currencies = array('RUB', 'RUR');

    protected $settings = array(
        'ShopID' => null,
        'ApiID' => null,
        'ApiPassword' => null,
        'ApiPullPassword' => null,
        'successUrl' => null,
        'failUrl' => null
    );

    protected $request_parameters = array(
        'PhoneNumber' => null
    );

    /**
     * Отправка счета на сервер QIWI и переадресация клиента для оплаты
     *
     * @param nc_payment_invoice $invoice
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        $phone_number = $this->get_request_parameter('PhoneNumber');
        $current_site = nc_core::get_object()->catalogue->get_current();
        $data = array(
            'user' => 'tel:' . $phone_number,
            'amount' => $invoice->get_amount(),
            'ccy' => 'RUB',
            'comment' => $invoice->get_description(),
            'lifetime' => date(DATE_ATOM, strtotime('+45 days')),
            'prv_name' => $current_site['Catalogue_Name']
        );

        // Чтобы QIWI не путал между собой счета с одинаковыми ID (но из разных копий NetCat),
        // добавляем к ID счета хэш от номера телефона, суммы и даты заказа
        $bill_id_hash = base_convert(abs(crc32($phone_number . $invoice->get_amount() . $invoice->get('created'))), 10, 32);
        $bill_id = $invoice->get_id() . '_' . $bill_id_hash;

        $ch = curl_init('https://api.qiwi.com/api/v2/prv/' . $this->get_setting('ShopID') . '/bills/' . $bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->get_setting('ApiID') . ':' . $this->get_setting('ApiPassword'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_exec($ch);
        curl_close($ch);

        $url_params = array(
            'shop' => $this->get_setting('ShopID'),
            'transaction' => $bill_id,
            'successUrl' => $this->get_setting('successUrl') ?: null,
            'failUrl' => $this->get_setting('failUrl') ?: null,
        );
        $url = 'https://qiwi.com/order/external/main.action?' . http_build_query($url_params, '', '&');

        header('Location: ' . $url);
    }

    /**
     * Действия при обработке каллбека от QIWI
     *
     * @param nc_payment_invoice $invoice
     */
    public function on_response(nc_payment_invoice $invoice = null) {
        switch ($this->get_response_value('status')) {
            case 'paid':
                $this->on_payment_success($invoice);
                break;
            case 'rejected':
                $this->on_payment_rejected($invoice);
                break;
            case 'waiting':
                $invoice->set('status', nc_payment_invoice::STATUS_WAITING)->save();
        }

        $this->return_status(0);
    }

    /**
     * Вывод ответа для внешнего запроса от QIWI
     * (любой статус кроме '0' QIWI трактует как ошибку запроса)
     *
     * @param int $status
     */
    public function return_status($status) {
        $status = intval($status);

        header_remove();
        ob_clean();

        header('Content-Type: text/xml');
        echo <<<HERE
<?xml version='1.0'?>
<result>
    <result_code>$status</result_code>
</result>
HERE;
    }

    /**
     * Проверка настроек платежной системы при запросе на оплату
     */
    public function validate_payment_request_parameters() {
        foreach ($this->get_settings_list() as $setting) {
            if (!$this->get_setting($setting) && !in_array($setting, array('successUrl', 'failUrl'))) {
                $this->add_error(sprintf(NETCAT_MODULE_PAYMENT_QIWI_ERROR_EMPTY_SETTING, $setting));
            }
        }

        if (!preg_match(self::PHONE_REGEXP, $this->get_request_parameter('PhoneNumber'))) {
            $this->add_error(NETCAT_MODULE_PAYMENT_QIWI_ERROR_WRONG_PHONE);
        }
    }

    /**
     * Возвращает форму заполнения деталей платежа
     * (Добавляем инпут для номера телефона покупателя: QIWI требует знать номер для выставления счета.
     * Если покупатель зарегистрирован, номер будет подставлен из $invoice->get('customer_phone'))
     *
     * @param nc_payment_invoice $invoice
     * @param int $show
     * @return string
     */
    public function get_request_form(nc_payment_invoice $invoice, $show = 1) {
        global $AUTH_USER_ID;
        $invoice_phone = '';

        if ($AUTH_USER_ID && $invoice->get('customer_id') == $AUTH_USER_ID) {
            $invoice_phone = nc_normalize_phone_number($invoice->get('customer_phone'));
        }

        $result = "<form action='" . $this->get_request_script_path() . "' method='post' target='_blank' id='nc_payment_form_qiwi'>";
        $result .= $this->make_input('invoice_id', $invoice->get_id());
        $result .= $this->make_input('payment_system', get_class($this));
        $result .= "<label for='nc_payment_form_qiwi_phone' class='nc-payment-form-qiwi-caption'>" . NETCAT_MODULE_PAYMENT_QIWI_SET_PHONE . "</label> ";
        $result .= "<input id='nc_payment_form_qiwi_phone' type='text' name='param_PhoneNumber' placeholder='+7__________' value='" . htmlspecialchars($invoice_phone, ENT_QUOTES) . "' autofocus>";
        $result .= "<input type='submit' value='" . NETCAT_MODULE_PAYMENT_FORM_PAY . "'>";
        $result .= "</form>";

        $result .= "<script>";
        $result .= "document.getElementById('nc_payment_form_qiwi').onsubmit = function() {";
        $result .= "    if (!this.param_PhoneNumber.value.match(" . self::PHONE_REGEXP . ")) {";
        $result .= "        alert('" . NETCAT_MODULE_PAYMENT_QIWI_SET_PHONE . "');";
        $result .= "        this.param_PhoneNumber.focus();";
        $result .= "        return false;";
        $result .= "    }";
        $result .= "}";
        $result .= "</script>";

        return $result;
    }


    /**
     * Обработка параметров входящего внешнего запроса
     *
     * @param nc_payment_invoice|null $invoice
     * @return bool
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        if ($_SERVER['PHP_AUTH_USER'] == $this->get_setting('ShopID')) {
            if ($_SERVER['PHP_AUTH_PW'] == $this->get_setting('ApiPullPassword')) {
                return true;
            }
        }

        $this->return_status(400);
        exit;
    }

    /**
     * Получение объекта nc_payment_invoice по параметрам входящего внешнего запроса
     *
     * @return nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        $bill_id = explode('_', str_replace('_TEST_', '', $this->get_response_value('bill_id')));
        $invoice_id = intval($bill_id[0]);
        return $this->load_invoice($invoice_id);
    }
}
