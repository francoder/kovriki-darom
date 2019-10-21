<?php

class nc_payment_system_platron extends nc_payment_system {

    const ERROR_MERCHANT_ID = NETCAT_MODULE_PAYMENT_PLATRON_ERROR_MERCHANT_ID_IS_NOT_VALID;
    const ERROR_SECRET_KEY = NETCAT_MODULE_PAYMENT_PLATRON_ERROR_SECRET_KEY_IS_NOT_VALID;
    const ERROR_SIGN_IS_NOT_VALID = NETCAT_MODULE_PAYMENT_PLATRON_ERROR_SIGN_IS_NOT_VALID;

    const TARGET_URL = "https://platron.ru/payment.php";

    protected $automatic = true;

    // принимаемые валюты
    protected $accepted_currencies = array('USD', 'EUR', 'RUB', 'RUR');
    protected $currency_map = array('RUR' => 'RUB');

    // параметры сайта в платежной системе
    protected $settings = array(
        'merchant_id' => null,
        'secret_key' => null,
        'lifetime' => 0,
        'testmode' => 0,
        'success_url' => null,
        'failure_url' => null,
    );

    // передаваемые параметры
    protected $request_parameters = array( // 'InvId' => null,
        // 'InvDesc' => null,
    );

    // получаемые параметры
    protected $callback_response = array(
        'InvId' => null,
        'OutSum' => null,
    );

    /**
     * @param nc_payment_invoice $invoice
     */
    public function execute_payment_request(nc_payment_invoice $invoice) {
        $currency_code = $this->get_currency_code($invoice->get_currency());

        $script_url = nc_get_scheme() . '://' . $_SERVER['HTTP_HOST'] . nc_module_path('payment') .
            'callback.php?paySystem=nc_payment_system_platron&invoice_id=' . $invoice->get_id();

        $data = array(
            'pg_merchant_id' => $this->get_setting('merchant_id'),
            'pg_order_id' => $invoice->get('order_id'),
            'pg_currency' => $currency_code,
            'pg_amount' => $invoice->get_amount('%0.2F'),
            'pg_lifetime' => $this->get_setting('lifetime') * 60, // в секундах
            'pg_testing_mode' => $this->get_setting('testmode'),
            'pg_user_ip' => $_SERVER['REMOTE_ADDR'],
            'pg_description' => mb_substr($invoice->get_description(), 0, 255, 'UTF-8'),
            'pg_check_url' => $script_url . '&type=check',
            'pg_result_url' => $script_url . '&type=result',
            'pg_request_method' => 'POST',
            'pg_salt' => rand(21, 43433), // Параметры безопасности сообщения. Необходима генерация pg_salt и подписи сообщения.
            'cms_payment_module' => 'Netcat',
        );

        if ($this->get_setting('success_url')) {
            $data['pg_success_url'] = $this->get_setting('success_url');
        }

        if ($this->get_setting('failure_url')) {
            $data['pg_failure_url'] = $this->get_setting('failure_url');
        }

        $phone_number = nc_normalize_phone_number($invoice->get('customer_phone'));
        if ($phone_number) {
            $data['pg_user_phone'] = $phone_number;
        }

        $email = $invoice->get('customer_email');
        if ($email) {
            $data['pg_user_email'] = $email;
            $data['pg_user_contact_email'] = $email;
        }

        $data['pg_sig'] = PG_Signature::make('payment.php', $data, $this->get_setting('secret_key'));
        ob_end_clean();
        $form = "
            <html>
              <body>
                    <form action='" . nc_payment_system_platron::TARGET_URL . "' method='POST'>" .
            $this->make_inputs($data)
            . "
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
    }

    /**
     *
     */
    public function validate_payment_request_parameters() {
        if (!$this->get_setting('merchant_id')) {
            $this->add_error(nc_payment_system_platron::ERROR_MERCHANT_ID);
        } elseif (!$this->get_setting('secret_key')) {
            $this->add_error(nc_payment_system_platron::ERROR_SECRET_KEY);
        }

    }

    /**
     * @param nc_payment_invoice $invoice
     */
    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
        if (empty($_POST['pg_sig']) || !PG_Signature::check($_POST['pg_sig'], PG_Signature::getOurScriptName(), $_POST, $this->get_setting('secret_key'))) {
            die('Wrong signature');
        }

        // Проверка существования счёта
        if (!$invoice) {
            $response_description = 'Счёт ' . ($this->get_response_value('invoice_id')) . ' не существует';
            $this->respond_with_xml('error', $response_description);
        }

        // Проверка статуса счёта
        $invoice_status_id = $invoice->get('status');
        $unacceptable_invoice_statuses = array(
            nc_payment_invoice::STATUS_SUCCESS => 'Счёт уже оплачен',
            nc_payment_invoice::STATUS_REJECTED => 'Счёт отклонён',
        );

        if (isset($unacceptable_invoice_statuses[$invoice_status_id])) {
            $response_status = $this->get_response_value('pg_can_reject') ? 'rejected' : 'error';
            $response_description = $unacceptable_invoice_statuses[$invoice_status_id];
            $this->respond_with_xml($response_status, $response_description);
        }

        switch ($this->get_response_value('type')) {
            case 'check':
                // все необходимые проверки выполнены выше: подпись, наличие счёта, его статус
                $this->respond_with_xml('ok');
                break;

            case 'result':
                // все необходимые проверки выполнены выше: подпись, наличие счёта, его статус
                if ($this->get_response_value('pg_result') == 1) {
                    $this->on_payment_success($invoice);
                    $response_description = 'Оплата принята';
                } else {
                    $this->on_payment_failure($invoice);
                    $response_description = 'Оплата не принята';
                }

                $this->respond_with_xml('ok', $response_description);
                break;

            default:
                die('Wrong request type');
        }
    }

    /**
     * @return bool|nc_payment_invoice
     */
    public function load_invoice_on_callback() {
        return $this->load_invoice($this->get_response_value('invoice_id'));
    }

    /**
     * @param string $status
     * @param null|string $description
     */
    protected function respond_with_xml($status, $description = null) {
        $data = array();
        $data['pg_status'] = $status;
        if (strlen($description)) {
            $description_key = ($status === 'ok' ? 'pg_description' : 'pg_error_description');
            $data[$description_key] = $description;
        }
        $data['pg_salt'] = $this->get_response_value('pg_salt'); // в ответе необходимо указывать тот же pg_salt, что и в запросе
        $data['pg_sig'] = PG_Signature::make(PG_Signature::getOurScriptName(), $data, $this->get_setting('secret_key'));

        $xml_response = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
        foreach ($data as $key => $value) {
            $xml_response->addChild($key, $value);
        }

        header('Content-type: text/xml');
        print $xml_response->asXML();
        exit();
    }
}

class PG_Signature {

    /**
     * Get script name from URL (for use as parameter in self::make, self::check, etc.)
     *
     * @param string $url
     * @return string
     */
    public static function getScriptNameFromUrl($url) {
        $path = parse_url($url, PHP_URL_PATH);
        $len = strlen($path);
        if ($len == 0 || '/' == $path{$len - 1}) {
            return "";
        }
        return basename($path);
    }

    /**
     * Get name of currently executed script (need to check signature of incoming message using self::check)
     *
     * @return string
     */
    public static function getOurScriptName() {
        return self::getScriptNameFromUrl($_SERVER['PHP_SELF']);
    }

    /**
     * Creates a signature
     *
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    public static function make($strScriptName, $arrParams, $strSecretKey) {
        return md5(self::makeSigStr($strScriptName, $arrParams, $strSecretKey));
    }

    /**
     * Verifies the signature
     *
     * @param string $signature
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return bool
     */
    public static function check($signature, $strScriptName, $arrParams, $strSecretKey) {
        return (string)$signature === self::make($strScriptName, $arrParams, $strSecretKey);
    }


    /**
     * Returns a string, a hash of which coincide with the result of the make() method.
     * WARNING: This method can be used only for debugging purposes!
     *
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    static function debug_only_SigStr($strScriptName, $arrParams, $strSecretKey) {
        return self::makeSigStr($strScriptName, $arrParams, $strSecretKey);
    }


    private static function makeSigStr($strScriptName, $arrParams, $strSecretKey) {
        unset($arrParams['pg_sig']);

        ksort($arrParams);

        array_unshift($arrParams, $strScriptName);
        array_push($arrParams, $strSecretKey);

        return join(';', $arrParams);
    }

    /********************** singing XML ***********************/

    /**
     * make the signature for XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     */
    public static function makeXML($strScriptName, $xml, $strSecretKey) {
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::make($strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Verifies the signature of XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return bool
     */
    public static function checkXML($strScriptName, $xml, $strSecretKey) {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = new SimpleXMLElement($xml);
        }
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::check((string)$xml->pg_sig, $strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Returns a string, a hash of which coincide with the result of the makeXML() method.
     * WARNING: This method can be used only for debugging purposes!
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     */
    public static function debug_only_SigStrXML($strScriptName, $xml, $strSecretKey) {
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::makeSigStr($strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Returns flat array of XML params
     *
     * @param (string|SimpleXMLElement) $xml
     * @return array
     */
    private static function makeFlatParamsXML($xml, $parent_name = '') {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = new SimpleXMLElement($xml);
        }

        $arrParams = array();
        $i = 0;
        foreach ($xml->children() as $tag) {

            $i++;
            if ('pg_sig' == $tag->getName()) {
                continue;
            }

            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $tag->getName() . sprintf('%03d', $i);

            if ($tag->children()->count() > 0) {
                $arrParams = array_merge($arrParams, self::makeFlatParamsXML($tag, $name));
                continue;
            }

            $arrParams += array($name => (string)$tag);
        }

        return $arrParams;
    }
}