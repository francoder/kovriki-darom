<?php

class nc_payment_system_cloudpayments extends nc_payment_system {

    const PAYMENT_RESULT_SUCCESS = 0;
    const PAYMENT_RESULT_ERROR_INVALID_ORDER = 10;
    const PAYMENT_RESULT_ERROR_INVALID_COST = 11;
    const PAYMENT_RESULT_ERROR_NOT_ACCEPTED = 13;
    const PAYMENT_RESULT_ERROR_EXPIRED = 20;

    protected $automatic = true;

    protected $accepted_currencies = array(
        'RUB',
        'RUR',
        'USD',
        'EUR',
        'GBP',
        'UAH',
        'BYR',
        'BYN',
        'KZT',
        'AZN',
        'CHF',
        'CZK',
        'CAD',
        'PLN',
        'SEK',
        'TRY',
        'CNY',
        'INR',
        'BRL',
        'ZAL',
        'UZS',
    );

    protected $currency_map = array('RUR' => 'RUB');

    // параметры сайта в платежной системе
    protected $settings = array(
        'public_id'       => null,
        'secret_key'      => null,
        'language'        => 'ru-RU',
        'enable_dms'      => '0',
        'enable_kkt'      => '0',
        'taxation_system' => null,
        'success_url'     => null,
        'fail_url'        => null,
    );

    static protected $vat_map = array(
        ''   => '',
        0    => '0',
        10   => '10',
        18   => '18',
    );
    static protected $cloudpayments_default_vat = '18';

    public function can_send_receipt_data_with_invoice() {
        return boolval($this->get_setting('enable_kkt'));
    }

    protected function execute_payment_request(nc_payment_invoice $invoice) {
        $charset = nc_core()->NC_CHARSET;
        $form    = $this->get_request_form($invoice);
        $lang    = array(
            'order' => NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_ORDER,
            'buyer' => NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_BUYER
        );

        if (nc_module_check_by_keyword('netshop') && $invoice->get('order_source') === 'netshop') {
            $human_amount = nc_netshop::get_instance()->format_price($invoice->get_amount());
        } else {
            $human_amount = number_format($invoice->get_amount(), 2, '.', ' ') .
                ' ' . $this->get_currency_code($invoice->get_currency());
        }

        $html = "<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset='{$charset}'>
    <title>{$invoice->get_description()}</title>
    
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        .payment-box {
            width: 600px;
            margin: 0 auto;
            padding-top: 20px;
            font-family: Verdana, Helvetica, sans-serif;
        }
        h2 {
            margin: 26px;
            color: #000;
            font-size: 36px;
            line-height: 38px;
        }
        .payment-holder {
            margin: 0 26px 26px;
            border-top: 2px solid #e6e6e6;
            border-bottom: 2px solid #e6e6e6;
            padding: 18px 0 0;    	
        }
        .sum {
            overflow: hidden;
            font-size: 24px;
            line-height: 26px;
            margin: 0;
            padding: 0 0 15px;
        }
        .sum dt {
            max-width: calc(100% - 145px);
            float: left;
        }
        .sum dd {
            float: none;
            overflow: hidden;
            text-align: right;
        }
        .payment-info {
            font-size: 24px;
            line-height: 26px;    	
            margin: 0;
        }
        .payment-info dt {
            float: left;
            width: 110px;
            text-align: right;
            font: 600 14px/16px Verdana, Helvetica, sans-serif;
            margin: 8px 15px 0 0;
            color: #6d7275;
            text-transform: uppercase;
        }
        .payment-info dd {
            overflow: hidden;
            margin: 0;
            padding: 0 0 7px;
        }
        input[type=submit] {
            background: #102889;
            color: #FFFFFF;
            cursor: pointer;
            border: none;
            padding: 0 20px;
            text-align: center;
            font-weight: 400;
            line-height: 40px;
            font-size: 18px;
            height: 40px;
            margin: 0;
        }
        .card-payment-buttons {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='payment-box'>
        <h2>{$invoice->get_description()}</h2>
        <div class='payment-holder'>
            <dl class='sum'>
                <dt>{$lang['order']} #{$invoice->get('order_id')}</dt>
                <dd>{$human_amount}</dd>
            </dl>
            <dl class='payment-info'>
                <dt>{$lang['buyer']}:</dt>
                <dd>{$invoice->get('customer_email')}</dd>
            </dl>
        </div>
        <div class='card-payment-buttons'>
            {$form}
        </div>
    </div>
</body>
</html>";

        echo $html;
    }

    public function validate_payment_request_parameters() {
        if (!$this->get_setting('public_id')) {
            $this->add_error(NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_ERROR_PUBLIC_ID_IS_NULL);
        }

        if (!($this->get_setting('secret_key'))) {
            $this->add_error(NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_ERROR_SECRET_KEY_IS_NULL);
        }
    }

    public function get_request_form(nc_payment_invoice $invoice, $show = true, $open_in_new_window = true) {
        $charge_method = $this->get_setting('enable_dms') ? 'auth' : 'charge';

        $params = array(
            'publicId'    => $this->get_setting('public_id'),  //id из личного кабинета
            'description' => $invoice->get_description(), //назначение
            'amount'      => floatval($invoice->get_amount()), //сумма
            'currency'    => $this->get_currency_code($invoice->get_currency()), //валюта
            'invoiceId'   => intval($invoice->get('order_id')), //номер заказа  (необязательно)
            'accountId'   => $invoice->get('customer_email'), //идентификатор плательщика (необязательно)
            'data'        => array(
                'name'          => $invoice->get('customer_name'),
                'phone'         => $invoice->get('customer_phone'),
                'cloudPayments' => array(),
                //В InvoiceId передаем id заказа, чтобы в ПУ CloudPayments отображался номер заказа, а не счета
                //Номер счета передаем в пользотельском параметре и по нему находим счет в callback
                'nc_invoice_id' => $invoice->get_id()
            )
        );
        if (intval($this->get_setting('enable_kkt'))) {
            $params['data']['cloudPayments']['customerReceipt'] = $this->get_receipt($invoice);
        }

        $lang = $this->get_setting('language');
        if (empty($lang)) {
            $lang = nc_core::get_object()->lang->detect_lang() === 'Russian' ? 'ru-RU' : 'en-US';
        }
        $params = nc_array_json($params);

        $success_url = $this->get_setting('success_url');
        if (empty($success_url)) {
            $success_url = '/';
        }
        $fail_url = $this->get_setting('fail_url');
        if (empty($fail_url)) {
            $fail_url = '/';
        }

        $result = "<script src=\"https://widget.cloudpayments.ru/bundles/cloudpayments\"></script>" . PHP_EOL;
        $result .= "<form id='nc_module_payment_system_cloudpayments_form' method='post'>";
        $result .= "<input type='submit' value='" . NETCAT_MODULE_PAYMENT_FORM_PAY . "'>";
        $result .= "</form>" . PHP_EOL;
        $result .= <<<SCRIPT
        <script>
            (function(show_widget_callback) {
                var form = document.getElementById('nc_module_payment_system_cloudpayments_form');
                if (form.addEventListener) {
                    form.addEventListener('click', show_widget_callback, false);
                } else {
                    form.attachEvent('onclick', show_widget_callback);
                }
            })(function(e) {
                var evt = e || window.event; // Совместимость с IE8
                if (evt.preventDefault) {  
                    evt.preventDefault();  
                } else {  
                    evt.returnValue = false;  
                    evt.cancelBubble=true;  
                }
                var widget = new cp.CloudPayments({language: '{$lang}'});
                widget.{$charge_method}({$params}, '{$success_url}', '{$fail_url}');
            });
        </script>
SCRIPT;
        return $result;
    }

    private function get_receipt(nc_payment_invoice $invoice) {
        $tax_system = $this->get_setting('taxation_system');
        if (empty($tax_system)) {
            $tax_system = '0';
        }
        $receipt_data = array(
            'Items'          => array(),
            'taxationSystem' => $tax_system,
            'email'          => $invoice->get('customer_email'),
            'phone'          => $invoice->get('customer_phone')
        );

        $items = $invoice->get_items();
        /** @var nc_payment_invoice_item $item */
        foreach ($items as $item) {
            $receipt_data['Items'][] = array(
                'label'    => $item->get('name'),
                'price'    => sprintf('%0.2F', $item->get('item_price')),
                'quantity' => floatval($item->get('qty')),
                'amount'   => sprintf('%0.2F', $item->get('total_price')),
                'vat'      => nc_array_value(self::$vat_map, $item->get('vat_rate'), self::$cloudpayments_default_vat)
            );
        }

        return $receipt_data;
    }

    public function load_invoice_on_callback() {
        $data = $this->get_response_value('Data');
        $data = json_decode($data, true);

        return $this->load_invoice(nc_array_value($data, 'nc_invoice_id'));
    }

    /**
     * @param nc_payment_invoice|null $invoice
     * @return void
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        //Check HMAC sign
        $post_data    = file_get_contents('php://input');
        $check_sign   = base64_encode(hash_hmac('SHA256', $post_data, $this->get_setting('secret_key'), true));
        $request_sign = isset($_SERVER['HTTP_CONTENT_HMAC']) ? $_SERVER['HTTP_CONTENT_HMAC'] : '';

        if ($check_sign !== $request_sign) {
            $this->callback_error(self::PAYMENT_RESULT_ERROR_NOT_ACCEPTED, $invoice, "Invalid HMAC Sign");
        };

        //Check required fields
        $required_fields = array(
            'InvoiceId',
            'Amount',
            'nc_action'
        );

        foreach ($required_fields as $f) {
            if (!$this->get_response_value($f)) {
                $this->callback_error(self::PAYMENT_RESULT_ERROR_NOT_ACCEPTED, $invoice, 'Field required ' . $f);
            }
        }

        if (!$invoice) {
            $this->callback_error(self::PAYMENT_RESULT_ERROR_INVALID_ORDER, $invoice);
        }

        if ($invoice->get_amount() != $this->get_response_value('Amount')) {
            $this->callback_error(self::PAYMENT_RESULT_ERROR_INVALID_COST, $invoice, "Invalid order cost");
        }
    }

    protected function on_response(nc_payment_invoice $invoice = null) {
        $action = $this->get_response_value('nc_action');
        switch ($action) {
            case 'pay':
                if ($this->get_response_value('Status') == 'Completed') {
                    $this->on_payment_success($invoice);
                } else if ($this->get_response_value('Status') == 'Authorized') {
                    $this->mark_order_authorized($invoice);
                }
                break;
            case 'confirm':
                $this->on_payment_success($invoice);
                break;
            case 'fail':
                $this->on_payment_failure($invoice);
                break;
            case 'refund':
                $this->on_payment_rejected($invoice);
                break;
        }
        $this->print_callback_response(self::PAYMENT_RESULT_SUCCESS);
    }

    private function callback_error($code, nc_payment_invoice $invoice = null, $msg = '') {
        if ($msg) {
            $this->add_error($msg);
        }

        $this->on_payment_failure($invoice);
        $this->print_callback_response($code);
        die();
    }

    private function print_callback_response($code) {
        header('Content-Type: application/json');
        echo json_encode(array('code' => $code));
    }

    private function mark_order_authorized(nc_payment_invoice $invoice) {
        if (nc_module_check_by_keyword('netshop') && $invoice->get('order_source') === 'netshop') {
            $order = nc_netshop::get_instance($invoice->get('catalogue_id'))->load_order($invoice->get('order_id'));
            if ($order && $order->has_property('ManagerComments')) {
                //Если у заказа уже есть комментарий, то вставялем первой строкой
                $new_comment = NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_PAYMENT_AUTHORIZED;
                $old_comment = $order->get('ManagerComments');
                if (!empty($old_comment)) {
                    $new_comment .= PHP_EOL . $old_comment;
                }
                $order->set('ManagerComments', $new_comment)->save();
            }
        }
    }
}