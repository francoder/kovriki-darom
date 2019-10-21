<?php

/**
 * Фискальный чек для счёта
 */
class nc_payment_receipt extends nc_record {

    /** чек только что создан, ещё не отправлен в ККМ */
    const STATUS_NEW = 'new';

    /** чек отправлен в ККМ, ожидается ответ */
    const STATUS_PENDING = 'pending';

    /** чек успешно зарегистрирован в ККМ */
    const STATUS_REGISTERED = 'registered';

    /** ККМ отказалась регистрировать чек из-за ошибки */
    const STATUS_FAILED = 'failed';

    /** не удалось подключиться к ККМ */
    const STATUS_CONNECTION_ERROR = 'connection_error';

    /** чек был отправлен в ККМ, но ответ не получен и повторных попыток предприниматься не будет */
    const STATUS_CANCELLED = 'cancelled';

    /** чек зарегистрирован через кассовый сервис (nc_payment_register_provider) */
    const PROVIDER_TYPE_REGISTER = 'register';

    /** чек зарегистрирован через платёжную систему (nc_payment_system) */
    const PROVIDER_TYPE_SYSTEM = 'system';


    protected $properties = array(
        'id' => null,
        'created' => null, // время создания записи в Netcat
        'invoice_id' => null,
        'status' => self::STATUS_NEW,
        'operation' => nc_payment::OPERATION_SELL, // тип операции чека (приход, возврат прихода)
        'amount' => null,                    // сумма чека
        'register_provider_type' => self::PROVIDER_TYPE_REGISTER, // способ отправки чека (через платёжную систему или через кассовые сервисы)
        'register_provider_id' => null,      // ID класса интеграции с ККМ в списке PaymentRegister или платёжной системы в PaymentSystem
        'transaction_id' => null,            // ID транзакции по обработке чека во внешней системе (например, в балансировщике)
        'fiscal_receipt_created' => null,    // время регистрации в ККМ
        'fiscal_receipt_number' => null,     // номер фискального чека в смене
        'shift_number' => null,              // номер смены
        'fiscal_storage_number' => null,     // номер фискального накопителя
        'register_registration_number' => null, // регистрационный номер ККМ
        'fiscal_document_number' => null,    // фискальный номер документа
        'fiscal_document_attribute' => null, // фискальный признак документа

        // виртуальное свойство (см. self::get()):
        // registered_signed_amount
    );

    protected $primary_key = 'id';

    protected $table_name = 'Payment_Receipt';
    protected $mapping = array(
        'id' => 'Payment_Receipt_ID',
        'invoice_id' => 'Payment_Invoice_ID',
        'register_provider_type' => 'RegisterProvider_Type',
        '_generate' => true,
    );

    /** @var  @var nc_payment_invoice */
    protected $invoice;

    /**
     * @param array|int|null $values
     */
    public function __construct($values = null) {
        parent::__construct($values);
        if (!$this->get('created')) {
            $this->set('created', date("Y-m-d H:i:s"));
        }
    }

    /**
     * Сохранение в БД
     *
     * @throws nc_record_exception
     * @return static
     */
    public function save() {
        $is_new = !$this->get_id() && $this->get('status') == self::STATUS_NEW;
        parent::save();
        if ($is_new) {
            // логирование создания нового чека
            $this->save_status(self::STATUS_NEW);
        }
        return $this;
    }

    /**
     * Сохраняет статус и создаёт запись в журнале
     * @param $new_status
     * @param mixed $data
     * @return $this
     */
    public function save_status($new_status, $data = null) {
        $this->set('status', $new_status)->save();
        switch ($new_status) {
            case self::STATUS_NEW:
                $event_type = nc_payment_register::LOG_TYPE_EVENT;
                $message = NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_CREATED;
                break;

            case self::STATUS_PENDING:
                $event_type = nc_payment_register::LOG_TYPE_EVENT;
                $message = NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_SENT;
                break;

            case self::STATUS_REGISTERED:
                $event_type = nc_payment_register::LOG_TYPE_EVENT;
                $message = NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_GOT_REPORT;
                nc_payment_register::send_receipt_to_customer($this);
                break;

            case self::STATUS_FAILED:
                $event_type = nc_payment_register::LOG_TYPE_ERROR;
                $message = NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_ERROR;
                break;

            case self::STATUS_CONNECTION_ERROR:
                $event_type = nc_payment_register::LOG_TYPE_ERROR;
                $message = NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_NO_REPLY;
                break;

            case self::STATUS_CANCELLED:
                $event_type = nc_payment_register::LOG_TYPE_ERROR;
                $message = NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_CANCELLED;
                break;

            default:
                trigger_error(__METHOD__ . ": '$new_status' is not allowed", E_USER_WARNING);
                return $this;
        }

        $receipt_id = $this->get_id();
        $message = sprintf($message, $receipt_id);
        $invoice = $this->get_invoice();

        nc_payment_register::log(array(
            'Catalogue_ID' => $invoice ? $invoice->get('site_id') : null,
            'Payment_Receipt_ID' => $receipt_id,
            'ReceiptStatus' => $new_status,
            'EventType' => $event_type,
            'Message' => $message,
            'AdditionalData' => $data,
        ));

        if ($event_type == nc_payment_register::LOG_TYPE_ERROR) {
            nc_payment_register::send_receipt_warning_to_admin($message, $this);
        }

        return $this;
    }

    /**
     * @param nc_payment_invoice $invoice
     * @return $this
     */
    public function set_invoice(nc_payment_invoice $invoice) {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @return nc_payment_invoice|null
     */
    public function get_invoice() {
        if (!$this->invoice) {
            try {
                $this->invoice = new nc_payment_invoice($this->get('invoice_id'));
            } catch (nc_record_exception $e) {
                return null;
            }
        }
        return $this->invoice;
    }

    /**
     * @return nc_payment_invoice_item_collection
     */
    public function get_items() {
        $invoice = $this->get_invoice();
        if (!$invoice) {
            return new nc_payment_invoice_item_collection();
        }
        return $invoice->get_items()->where('operation', $this->get('operation'));
    }

    /**
     *
     * @param string $property ключ в массиве $this->properties
     * @return mixed значение
     * @throws nc_record_exception
     */
    public function get($property) {
        if ($property === 'registered_signed_amount') {
            return $this->get_registered_signed_amount();
        }
        return parent::get($property);
    }


    /**
     * Возвращает сумму для зарегистрированного в ККМ счёта (отрицательное значение для возврата)
     * @return int|float
     */
    public function get_registered_signed_amount() {
        if ($this->get('status') !== self::STATUS_REGISTERED) {
            return 0;
        }
        $sign = $this->get('operation') === nc_payment::OPERATION_SELL ? 1 : -1;
        return $sign * $this->get('amount');
    }

}
