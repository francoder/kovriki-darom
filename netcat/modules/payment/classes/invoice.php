<?php

/**
 * Счёт для платёжной системы.
 *
 * Основное предназначение счёта — получение недостающей суммы (или возврат переплаты)
 * через платёжную систему.
 *
 * Счёт может быть «первичным» (первым) и «корректировочным» (при изменении
 * состава или стоимости позиций счёта или при полном возврате ранее оплаченного
 * счёта).
 *
 * Каждый счёт имеет позиции, которые используются при формировании чеков.
 * Позиции счета могут быть двух типов (свойство 'operation'):
 * 1) продажа (nc_payment::OPERATION_SELL);
 * 2) возврат (nc_payment::OPERATION_SELL_REFUND), если счёт корректировочный.
 *
 * При возврате сумма счёта может быть отрицательной, в таком случае должен быть
 * выполнен возврат средств клиенту (НЕ РЕАЛИЗОВАНО / TBD).
 *
 * Счёту соответствуют чеки nc_payment_receipt для онлайн-касс.
 * Чеки, соответственно типам позиций, могут быть двух типов (свойство 'operation'):
 * 1) продажа (nc_payment::OPERATION_SELL);
 * 2) возврат (nc_payment::OPERATION_SELL_REFUND).
 *
 * Т. о. каждому счёту может соответствовать один или два чека (на продажу и на возврат).
 *
 */
class nc_payment_invoice extends nc_record {
    const STATUS_NEW = 1;
    const STATUS_SENT_TO_PAYMENT_SYSTEM = 2;
    const STATUS_CALLBACK_ERROR = 3;
    const STATUS_CALLBACK_WRONG_SUM = 4;
    const STATUS_WAITING = 5;
    const STATUS_SUCCESS = 6;
    const STATUS_REJECTED = 7;
    const STATUS_CANCELLED = 8;

    const TYPE_PRIMARY = 1; // первичный счёт
    const TYPE_CORRECTION = 2; // счёт для коррекции предыдущего счёта

    protected $primary_key = 'id';
    protected $properties = array(
        'id' => null,
        'type' => self::TYPE_PRIMARY,
        'payment_system_id' => 0,
        'amount' => 0,
        'description' => '',
        'currency' => 'RUB',
        'customer_id' => 0,
        'customer_email' => '',
        'customer_phone' => '',
        'customer_name' => '',
        'site_id' => null,           // !!!   может быть NULL у старых счетов
        'order_source' => '',
        'order_id' => 0,
        'status' => self::STATUS_NEW,
        'last_response' => '',
        'created' => null,
    );

    protected $table_name = 'Payment_Invoice';
    protected $mapping = array(
        'id' => 'Payment_Invoice_ID',
        'payment_system_id' => 'Payment_System_ID',
        'amount' => 'Amount',
        'description' => 'Description',
        'currency' => 'Currency',
        'customer_id' => 'Customer_ID',
        'customer_email' => 'Customer_Email',
        'customer_phone' => 'Customer_Phone',
        'customer_name' => 'Customer_Name',
        'site_id' => 'Catalogue_ID',
        'order_source' => 'Order_Source',
        'order_id' => 'Order_ID',
        'status' => 'Status',
        'last_response' => 'Last_Response',
        'created' => 'Created'
    );

    /** @var  null|nc_payment_invoice_item_collection позиции счёта */
    protected $items;
    /** @var  nc_payment_invoice_item_collection позиции счёта, которые были ранее сохранены в БД */
    protected $items_in_database;
    /** @var  null|nc_payment_receipt_collection */
    protected $receipts;
    /** @var  null|nc_payment_system */
    protected $payment_system;

    /**
     * @param array|int|null $values
     */
    public function __construct($values = null) {
        parent::__construct($values);
        if (!$this->get('created')) {
            $this->set('created', date('Y-m-d H:i:s'));
        }
        if (!$this->get('site_id')) {
            $this->set('site_id', nc_core::get_object()->catalogue->get_current('Catalogue_ID'));
        }
        $this->items_in_database = new nc_payment_invoice_item_collection();
    }

    /**
     * Возврат суммы счёта с опциональным её форматированием
     * @param null|string $format    формат для sprintf, например '%0.2F'
     * @return number|string
     */
    public function get_amount($format = null) {
        $amount = $this->get('amount');
        return $format ? sprintf($format, $amount) : $amount;
    }

    /**
     * @return string
     */
    public function get_description() {
        return $this->get('description');
    }

    /**
     * @return string
     */
    public function get_currency() {
        return $this->get('currency');
    }

    /**
     *
     * @param string $property ключ в массиве $this->properties
     * @param mixed $value новое значение
     * @param boolean $add_new_property добавить свойство, если оно не было ранее определено
     * @throws nc_record_exception
     * @return $this
     */
    public function set($property, $value, $add_new_property = false) {
        if (is_string($value)) {
            $value = trim($value);
        }

        return parent::set($property, $value, $add_new_property);
    }

    /**
     * Сохранение в БД
     *
     * @throws nc_record_exception
     * @return static
     */
    public function save() {
        parent::save();
        $this->save_items();
        return $this;
    }


    /**
     * Возвращает коллекцию с позициями счёта
     *
     * @return nc_payment_invoice_item_collection
     */
    public function get_items() {
        if (!$this->items) {
            $invoice_id = $this->get_id();
            if (!$invoice_id) {
                // пока нет ID счёта, не присваиваем $this->items:
                return new nc_payment_invoice_item_collection();
            }

            $this->items = new nc_payment_invoice_item_collection();
            $this->items->select_from_database("SELECT * FROM `%t%` WHERE `Payment_Invoice_ID` = $invoice_id");

            if ($this->items->count()) {
                $this->items_in_database = clone $this->items;
            } else if ($this->get('order_source') === 'netshop') {
                $this->get_items_from_netshop_order();
            }
            // fallback: счёт не из netshop без позиций, счёт для удалённого заказа из netshop
            if (!$this->items->count()) {
                return new nc_payment_invoice_item_collection(array($this->get_dummy_invoice_item()));
            }

            if ($this->get('status') == self::STATUS_SUCCESS) {
                foreach ($this->items as $item) {
                    $item->set('paid_qty', $item->get('qty'));
                }
            }
        }
        return $this->items;
    }

    /**
     * Заменяет позиции счёта
     * @param $items
     * @return $this
     */
    public function set_items(nc_payment_invoice_item_collection $items) {
        $this->items = $items;
        $this->set('amount', round($items->sum('signed_total_price'), 2));
        return $this;
    }

    /**
     * Сохраняет позиции счёта в БД
     */
    protected function save_items() {
        $current_items = $this->get_items();

        /** @var nc_payment_invoice_item $old_item */
        foreach ($this->items_in_database as $old_item_id => $old_item) {
            if (!$current_items->offsetExists($old_item_id)) {
                $old_item->delete();
            }
        }

        $invoice_id = $this->get_id();
        foreach ($current_items as $item) {
            $item->set('invoice_id', $invoice_id)->save();
        }

        $this->items_in_database = clone $this->items;
    }

    /**
     * Возвращает позицию счёта с описанием и суммой всего счёта
     * (fallback для заказов не из ИМ, у которых не указаны позиции счёта)
     * @return nc_payment_invoice_item
     */
    protected function get_dummy_invoice_item() {
        return new nc_payment_invoice_item(array(
            'invoice_id' => $this->get_id(),
            'operation' => nc_payment::OPERATION_SELL,
            'name' => $this->get_description(),
            'item_price' => (float)$this->get_amount(),
            'qty' => 1,
            'vat_rate' => null,
            'source_component_id' => null,
            'source_item_id' => null,
        ));
    }

    /**
     * Создаёт позиции счёта из заказа интернет-магазина.
     * (Метод находится в nc_payment_invoice для совместимости с существующими
     * шаблонами добавления заказа — для автоматической загрузки позиций без
     * необходимости менять действие после добавления заказа.)
     */
    protected function get_items_from_netshop_order() {
        if ($this->get('order_source') !== 'netshop' || !nc_module_check_by_keyword('netshop', false)) {
            return;
        }

        $order_id = $this->get('order_id');
        if (!$order_id) {
            return;
        }

        $netshop = nc_netshop::get_instance($this->get('site_id'));
        $order = $netshop->load_order($order_id);
        if (!$order) {
            return;
        }

        $invoice_id = $this->get_id();
        $default_vat_rate = $netshop->get_setting('VAT');
        $this->items = new nc_payment_invoice_item_collection();
        $order_items = $order->get_items_with_distributed_cart_discount(false);

        foreach ($order_items as $order_item) {
            $vat_rate = $order_item['VAT'];
            if (!strlen($vat_rate)) {
                $vat_rate = $default_vat_rate;
            }

            $this->items->add(new nc_payment_invoice_item(array(
                'invoice_id' => $invoice_id,
                'operation' => nc_payment::OPERATION_SELL,
                'name' => $order_item['FullName'] ?: "[$order_item[_ItemKey]]",
                'item_price' => $order_item['ItemPrice'],
                'qty' => $order_item['Qty'],
                'vat_rate' => $vat_rate,
                'source_component_id' => $order_item['Class_ID'],
                'source_item_id' => $order_item['Message_ID'],
            )));
        }

        if ($order['DeliveryPriceWithDiscount'] > 0) {
            $this->items->add(new nc_payment_invoice_item(array(
                'invoice_id' => $invoice_id,
                'operation' => nc_payment::OPERATION_SELL,
                'name' => NETCAT_MODULE_NETSHOP_DELIVERY,
                'item_price' => $order['DeliveryPriceWithDiscount'],
                'qty' => 1,
                'vat_rate' => $default_vat_rate,
                'source_component_id' => null,
                'source_item_id' => null,
            )));
        }

        if ($order['PaymentCost'] > 0) {
            $this->items->add(new nc_payment_invoice_item(array(
                'invoice_id' => $invoice_id,
                'operation' => nc_payment::OPERATION_SELL,
                'name' => NETCAT_MODULE_PAYMENT_PAYMENT_CHARGE,
                'item_price' => $order['PaymentCost'],
                'qty' => 1,
                'vat_rate' => $default_vat_rate,
                'source_component_id' => null,
                'source_item_id' => null,
            )));
        }
    }

    /**
     * Загружает чеки, связанные со счётом, либо (если чеков нет) создаёт их
     *
     * @param bool $create_new создать чеки, если их ещё нет
     * @return nc_payment_receipt_collection
     */
    public function get_all_receipts($create_new = true) {
        $payment_system = nc_payment_factory::create($this->get('payment_system_id'));

        if (!$payment_system || !$payment_system->is_automatic() || !in_array($this->get('currency'), array('RUB', 'RUR'))) {
            return new nc_payment_receipt_collection();
        }

        if ($this->receipts) {
            $result = $this->receipts;
        } else if ($this->get_id()) {
            // пробуем загрузить существующие
            $result = $this->load_receipts();
            // если их нет — создаём новые
            if ($create_new && !$result->count()) {
                $result = $this->create_and_save_new_receipts();
            }
            $this->receipts = $result;
        } else {
            trigger_error(__METHOD__ . ': unable to get receipts before invoice is saved', E_USER_WARNING);
            $result = new nc_payment_receipt_collection();
        }

        return $result;
    }

    /**
     * Возвращает чек продажи. Создаёт чек, если его ещё не было.
     *
     * @return nc_payment_receipt
     */
    public function get_sell_receipt() {
        return $this->get_all_receipts()
                    ->where('operation', nc_payment::OPERATION_SELL)
                    ->first();
    }

    /**
     * Возвращает чек возврата продажи. Создаёт чек, если его ещё не было.
     *
     * @return nc_payment_receipt
     */
    public function get_sell_refund_receipt() {
        return $this->get_all_receipts()
                    ->where('operation', nc_payment::OPERATION_SELL_REFUND)
                    ->first();
    }

    /**
     * @return nc_payment_receipt_collection
     */
    protected function load_receipts() {
        $result = new nc_payment_receipt_collection();
        $id = (int)$this->get_id();

        $result->select_from_database("SELECT * FROM `%t%` WHERE `Payment_Invoice_ID` = $id")
               ->each('set_invoice', $this);

        return $result;
    }

    /**
     * @return nc_payment_receipt_collection
     */
    protected function create_and_save_new_receipts() {
        $invoice_id = $this->get_id();

        // определяем, кто будет отвечать за отправку чека
        list($register_provider_type, $register_provider_id) = nc_payment_register::get_register_provider_type_and_id($this);

        // создаём чеки
        $result = new nc_payment_receipt_collection();
        $all_items = $this->get_items();

        foreach (array(nc_payment::OPERATION_SELL, nc_payment::OPERATION_SELL_REFUND) as $operation) {
            $items = $all_items->where('operation', $operation);
            if ($items->count()) {
                $receipt = new nc_payment_receipt(array(
                    'invoice_id' => $invoice_id,
                    'operation' => $operation,
                    'amount' => $items->sum('total_price'),
                    'register_provider_type' => $register_provider_type,
                    'register_provider_id' => $register_provider_id,
                ));
                $receipt->set_invoice($this)->save();
                $result->add($receipt);
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function get_customer_contact_for_receipt() {
        $email = $this->get('customer_email');
        if (!nc_check_email($email)) {
            $email = null;
        }
        return $email ?:
               nc_normalize_phone_number($this->get('customer_phone')) ?:
               nc_payment_register::get_default_customer_email();
    }

    /**
     * @param nc_payment_system $payment_system
     * @return $this
     */
    public function set_payment_system(nc_payment_system $payment_system) {
        $this->payment_system = $payment_system;
        return $this;
    }

    /**
     * @return nc_payment_system|null
     */
    public function get_payment_system() {
        if (!$this->payment_system) {
            $this->payment_system = nc_payment_factory::create($this->get('payment_system_id'), $this->get('site_id'));
        }
        return $this->payment_system;
    }

}