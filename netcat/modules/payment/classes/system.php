<?

/**
 * Абстрактный класс платежной системы
 *
 */

abstract class nc_payment_system {

    /**
     * Общие ошибки
     */
    const ERROR_INVOICE_NOT_FOUND = NETCAT_MODULE_PAYMENT_ERROR_INVOICE_NOT_FOUND;
    const ERROR_INVALID_SIGNATURE = NETCAT_MODULE_PAYMENT_ERROR_PRIVATE_SECURITY_IS_NOT_VALID;
    const ERROR_INVALID_SUM = NETCAT_MODULE_PAYMENT_ERROR_INVALID_SUM;

    /**
     * Названия генерируемых событий
     */
    const EVENT_ON_INIT = "onPayInit";
    const EVENT_BEFORE_PAY_REQUEST = "beforePayRequest";
    const EVENT_AFTER_PAY_REQUEST = "afterPayRequest";
    const EVENT_ON_PAY_REQUEST_ERROR = "onPayRequestError";
    const EVENT_BEFORE_PAY_CALLBACK = "beforePayCallback";
    const EVENT_AFTER_PAY_CALLBACK = "afterPayCallback";
    const EVENT_ON_PAY_CALLBACK_ERROR = "onPayCallbackError";
    const EVENT_ON_PAY_SUCCESS = "onPaySuccess";
    const EVENT_ON_PAY_FAILURE = "onPayFailure";
    const EVENT_ON_PAY_REJECTED = "onPayRejected";

    /**
     * @var int   ID платёжной системы (классификатор PaymentSystem)
     */
    protected $id;

    /**
     * @var boolean  TRUE — автоматический прием платежа, FALSE — ручная проверка
     */
    protected $automatic;

    /**
     * @var array  Коды валют, которые принимает платежная система (трехбуквенные коды ISO 4217)
     */
    protected $accepted_currencies = array('RUB');

    /**
     * @var array  Автоматический маппинг кодов валют из внешних в принятые в платежной системе
     */
    protected $currency_map = array();

    /**
     * @var array  Настройки платёжной системы
     */
    protected $settings = array();

    /**
     * @var array  Дополнительные (изменяемые) параметры запроса к платежной системе
     */
    protected $request_parameters = array();

    /**
     * @var array  Ответ платёжной системы, @see self::set_callback_response()
     */
    protected $callback_response = array();

    /**
     * @var array  Массив с ошибками
     */
    protected $errors = array();

    /**
     * Конструктор объекта платежной системы
     *
     */
    public function __construct() {
        $this->notify_listeners(nc_payment_system::EVENT_ON_INIT);
    }

    // --- Геттеры и сеттеры ---------------------------------------------------
    /**
     * @param int $id
     */
    public function set_id($id) {
        $this->id = (int)$id;
    }

    /**
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Установка значений параметров платёжной системы
     */
    public function set_settings(array $settings) {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * Значение параметра для платёжной системы
     */
    public function get_setting($setting_name) {
        return isset($this->settings[$setting_name])
            ? $this->settings[$setting_name]
            : null;
    }

    /**
     * Устанавливает дополнительный параметр запроса к платежной системе
     */
    public function set_request_parameters(array $params) {
        $this->request_parameters = array_merge($this->request_parameters, $params);
    }

    /**
     *
     */
    public function get_request_parameter($param) {
        return isset($this->request_parameters[$param])
            ? $this->request_parameters[$param]
            : null;
    }


    /**
     * Возвращает массив с параметрами сайта в платежной системе
     *
     * @return    array
     */
    public function get_settings_list() {
        return array_keys($this->settings);
    }
    
        
    /**
     * Регистрирует ответ от платежной системы (callback)
     * @see callback.php
     */
    protected function set_callback_response(array $response) {
        $this->callback_response = array_merge($this->callback_response, $response);
    }

    /**
     * Возвращает параметр ответа платежной системы
     */
    public function get_response_value($param) {
        return isset($this->callback_response[$param])
            ? $this->callback_response[$param]
            : null;
    }

    /**
     * Возвращает весь полученный ответ
     */
    public function get_response() {
        return $this->callback_response;
    }

    /**
     * Проверка правильности суммы
     */
    public function is_amount_valid($amount) {
        return is_numeric($amount) && $amount > 0;
    }

    /**
     * Возвращает код валюты с учётом $this->currency_map
     */
    protected function get_currency_code($iso_currency_code) {
        if (isset($this->currency_map[$iso_currency_code])) {
            return $this->currency_map[$iso_currency_code];
        }
        return $iso_currency_code;
    }

    /**
     * Проверяет, принимает ли платежная система указанную валюту
     * @param $currency_code
     * @return bool
     */
    public function is_currency_accepted($currency_code) {
        return in_array($currency_code, $this->accepted_currencies);
    }

    // --- Получение путей к обработчикам запросов/ответов ---------------------
    /**
     *
     */
    protected function get_module_url() {
        $domain = nc_core('catalogue')->get_current('Domain');
        return nc_core('catalogue')->get_url_by_host_name($domain) . nc_module_path('payment');
    }

    /**
     *
     */
    protected function get_request_script_path() {
        return nc_module_path('payment') . "pay_request.php";
    }

    /**
     *
     */
    protected function get_callback_script_url() {
        return $this->get_module_url() . "callback.php?paySystem=" . get_class($this);
    }

    // --- Хелперы для работы с формами ----------------------------------------
    /**
     *
     */
    protected function make_input($name, $value, $type = "hidden") {
        return "<input type='{$type}' name='" . htmlspecialchars($name, ENT_QUOTES) .
               "' value='" . htmlspecialchars($value, ENT_QUOTES) . "' />\n";
    }

    protected function make_inputs(array $values, $type = "hidden") {
        $result = "";
        foreach ($values as $name => $value) {
            $result .= $this->make_input($name, $value, $type);
        }
        return $result;
    }

    // --- Работа с запросами и ответами платёжной системы ---------------------

    /**
     * @param string $event_name
     * @param nc_payment_invoice $invoice
     */
    protected function notify_listeners($event_name, nc_payment_invoice $invoice = null) {
        nc_core('event')->execute($event_name, $this, $invoice);
    }

    /**
     * Добавление ошибки к массиву ошибок
     *
     * @param    string $string
     */
    protected function add_error($string) {
        $this->errors[] = $string;
    }

    /**
     * Возвращает массив ошибкой
     *
     * @return    array
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     *
     */
    public function reset_errors() {
        $this->errors = array();
    }

    /**
     * Возвращает значение свойства automatic
     *
     * @return    integer
     */
    public function is_automatic() {
        return $this->automatic;
    }

    /**
     * Запрос на проведение платежа
     *
     */
    final public function process_payment_request(nc_payment_invoice $invoice) {
        $this->notify_listeners(nc_payment_system::EVENT_BEFORE_PAY_REQUEST, $invoice);

        $this->validate_invoice($invoice);
        $this->validate_payment_request_parameters();

        if (!count($this->errors)) {

            $invoice->set('payment_system_id', $this->get_id())->save();
            $this->execute_payment_request($invoice);

            $invoice->set('status', nc_payment_invoice::STATUS_SENT_TO_PAYMENT_SYSTEM)->save();
            $this->notify_listeners(nc_payment_system::EVENT_AFTER_PAY_REQUEST, $invoice);

        } else {
            $this->notify_listeners(nc_payment_system::EVENT_ON_PAY_REQUEST_ERROR, $invoice);
            throw new nc_payment_exception(NETCAT_MODULE_PAYMENT_REQUEST_ERROR);
        }

    }

    /**
     * Запрос на обработку обратного вызова платежной системы
     *
     * @param array $response
     */
    final public function process_callback_response(array $response = array()) {
        $this->set_callback_response($response);

        $invoice = $this->load_invoice_on_callback();

        if ($invoice instanceof nc_payment_invoice) {
            // Если в ответе содержится не-UTF8-строки (например, когда PayPal не
            // настроен на его стороне), json_encode возвратит false.
            // Уберём не-UTF8-последовательности — лучше записать хоть что-то, чем ничего.
            $clean_utf = function($value) {
                return mb_convert_encoding($value, "UTF-8", "UTF-8");
            };

            $response_to_log = array_map($clean_utf, $response);

            $invoice->set('last_response', json_encode($response_to_log));
            $invoice->save();
        }
        else if ($invoice === false) {
            $this->add_error(NETCAT_MODULE_PAYMENT_CANNOT_LOAD_INVOICE_ON_CALLBACK);
            $invoice = null;
        }
        else {
            $invoice = null;
        }

        $this->validate_payment_callback_response($invoice);
        $this->notify_listeners(nc_payment_system::EVENT_BEFORE_PAY_CALLBACK, $invoice);

        if (!count($this->errors)) {
            $this->on_response($invoice);
            $this->notify_listeners(nc_payment_system::EVENT_AFTER_PAY_CALLBACK, $invoice);
        } else {
            $this->notify_listeners(nc_payment_system::EVENT_ON_PAY_CALLBACK_ERROR, $invoice);
        }
    }

    /**
     * Возвращает форму заполнения деталей платежа.
     * По умолчанию — POST-форма для выполнения запроса через скрипт pay_request.php
     * @param nc_payment_invoice $invoice
     * @param bool $show показать кнопку перехода к оплате (true, по умолчанию)
     *      или сразу перейти на сайт платёжной системы (false)
     * @param bool $open_in_new_window открывать сайт платёжной системы в новой вкладке
     *      (true, по умолчанию) или в этой же (false)
     * @return string
     */
    public function get_request_form(nc_payment_invoice $invoice, $show = true, $open_in_new_window = true) {
        $result = "<form action='" . $this->get_request_script_path() . "' method='post'" .
                  ($open_in_new_window ? " target='_blank'" : "") . "' id='nc_payment_form'>";
        $result .= $this->make_input('invoice_id', $invoice->get_id());
        $result .= $this->make_input('payment_system', get_class($this));
        $result .= $show ? "<input type='submit' value='" . NETCAT_MODULE_PAYMENT_FORM_PAY . "'>" : "";
        $result .= "</form>";
        $result .= !$show ? "<script type='text/javascript'>document.getElementById('nc_payment_form').submit();</script>" : "";

        return $result;
    }

    /**
     * Здесь должно происходить конкретное действие проведение платежа
     *
     * @param nc_payment_invoice $invoice
     * @return mixed
     */
    abstract protected function execute_payment_request(nc_payment_invoice $invoice);

    /**
     * Здесь должен осуществляться анализ обратного вызова платежной системы и
     * вызов методов on_payment_success() или on_payment_failure().
     * Не забудьте установить id заказа:
     * $this->set_order_id($this->get_response_value('ПолеОтветаСодержащееIdЗаказа'));
     *
     * @param nc_payment_invoice $invoice
     */
    abstract protected function on_response(nc_payment_invoice $invoice = null);

    /**
     * Здесь должна осуществляться проверка параметров для проведения платежа.
     * В случае ошибок вызывать метод add_error($string)
     *
     */
    abstract public function validate_payment_request_parameters();


    /**
     * Проверка корректности счёта
     * @param nc_payment_invoice $invoice
     */
    protected function validate_invoice(nc_payment_invoice $invoice) {
        if (!($invoice->get_id())) {
            $this->add_error(NETCAT_MODULE_PAYMENT_ORDER_ID_IS_NULL);
        }

        if (!$this->is_amount_valid($invoice->get_amount())) {
            $this->add_error(NETCAT_MODULE_PAYMENT_INCORRECT_PAYMENT_AMOUNT);
        }

        if (!$this->is_currency_accepted($invoice->get_currency())) {
            $error = sprintf(NETCAT_MODULE_PAYMENT_INCORRECT_PAYMENT_CURRENCY,
                             htmlspecialchars($invoice->get_currency()));

            $this->add_error($error);
        }
    }

    /**
     * Здесь должна осуществляться проверка параметров при поступлении обратного
     * вызова платежной системы.
     * В случае ошибок вызывать метод add_error($string)
     *
     * @param nc_payment_invoice $invoice
     */
    abstract public function validate_payment_callback_response(nc_payment_invoice $invoice = null);

    /**
     * Здесь могут быть выполнены действия при успешном платеже
     * @param nc_payment_invoice $invoice
     */
    protected function on_payment_success(nc_payment_invoice $invoice = null) {
        $invoice->set('status', nc_payment_invoice::STATUS_SUCCESS)->save();
        $this->notify_listeners(nc_payment_system::EVENT_ON_PAY_SUCCESS, $invoice);
        if ($invoice) {
            $this->process_invoice_receipts_after_successful_payment($invoice);
        }
    }

    /**
     * Здесь могут быть выполнены действия при неудачном платеже
     * @param nc_payment_invoice $invoice
     */
    protected function on_payment_failure(nc_payment_invoice $invoice = null) {
        $invoice->set('status', nc_payment_invoice::STATUS_CALLBACK_ERROR)->save();
        $this->notify_listeners(nc_payment_system::EVENT_ON_PAY_FAILURE, $invoice);
    }
    
    /**
     * Здесь могут быть выполнены действия при отказе от платежа
     * @param nc_payment_invoice $invoice
     */
    protected function on_payment_rejected(nc_payment_invoice $invoice = null) {
        $invoice->set('status', nc_payment_invoice::STATUS_REJECTED)->save();
        $this->notify_listeners(nc_payment_system::EVENT_ON_PAY_REJECTED, $invoice);
    }

    /**
     * Обрабатывает установку статуса «оплачен» вручную: вызывает обработчики событий, регистрирует чеки
     *
     * @param nc_payment_invoice $invoice
     */
    final public function process_manual_invoice_status_change(nc_payment_invoice $invoice) {
        if ($invoice->get('status') != nc_payment_invoice::STATUS_SUCCESS) {
            return;
        }

        $this->notify_listeners(nc_payment_system::EVENT_BEFORE_PAY_CALLBACK, $invoice);
        $this->notify_listeners(nc_payment_system::EVENT_AFTER_PAY_CALLBACK, $invoice);
        $this->process_invoice_receipts_after_successful_payment($invoice);
    }

    /**
     * Загрузка объекта платежа
     * @param int $invoice_id
     * @return nc_payment_invoice|boolean
     */
    protected function load_invoice($invoice_id) {
        $invoice_id = (int)$invoice_id;
        if (!$invoice_id) {
            return false;
        }

        try {
            return new nc_payment_invoice($invoice_id);
        }
        catch (nc_record_exception $e) {
            return false;
        }
    }

    /**
     * @return nc_payment_invoice|boolean
     */
    protected function load_invoice_on_callback() {
        return true;
    }

    // --- Работа с чеками ---

     /**
      * Возвращает возможность платёжной системы отправлять чеки продажи вместе со счётом
      *
      * @return bool
      */
    public function can_send_receipt_data_with_invoice() {
        return false;
    }

     /**
      * Возвращает возможность платёжной системы отправлять чеки продажи или возврата
      * независимо от операции возврата средств.
      * Если возвращает true, должен быть переопределён метод create_receipts().
      *
      * @return bool
      */
    public function can_send_custom_receipts() {
        return false;
    }

    /**
     * Обрабатывает чек. Должен вызываться только если can_create_custom_receipts()
     * возвращает true.
     *
     * @param nc_payment_receipt $receipt
     */
    public function process_receipt(nc_payment_receipt $receipt) {
    }

    /**
     * Создаёт кассовые чеки после успешной оплаты
     *
     * @param nc_payment_invoice $invoice
     */
    protected function process_invoice_receipts_after_successful_payment(nc_payment_invoice $invoice) {
        if ($invoice->get('status') != nc_payment_invoice::STATUS_SUCCESS) {
            return;
        }

        $is_primary = $invoice->get('type') == nc_payment_invoice::TYPE_PRIMARY;

        // Если система умеет отправлять чеки вместе со счётом, данные для чека должны были быть
        // отправлены вместе со счётом
        if ($is_primary && $this->can_send_receipt_data_with_invoice()) {
            return;
        }

        // Отправка чеков отключена, ничего не делаем
        if (!nc_payment_register::is_enabled($invoice->get('site_id')) && !$this->can_send_custom_receipts()) {
            return;
        }

        // [Сценарий автоматического возврата средств для корректировочного счёта не реализован]

        /** @var nc_payment_receipt $receipt */
        foreach ($invoice->get_all_receipts() as $receipt) {
            $receipt_status = $receipt->get('status');
            if ($receipt_status === nc_payment_receipt::STATUS_PENDING || $receipt_status === nc_payment_receipt::STATUS_REGISTERED) {
                continue;
            }
            nc_payment_register::route_receipt_processing($receipt);
        }
    }

}
