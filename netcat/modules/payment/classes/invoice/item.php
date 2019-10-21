<?php

class nc_payment_invoice_item extends nc_record {

    protected $properties = array(
        'id' => null,
        'invoice_id' => null,
        'operation' => nc_payment::OPERATION_SELL, // тип позиции счёта (продажа или возврат)
        'name' => '', // название позиции
        'item_price' => 0, // цена за 1 единицу (с учётом скидок)
        'qty' => 1, // количество (может быть float)
        'vat_rate' => null, // ставка НДС в процентах (null — не облагается НДС)
        'source_component_id' => null, // компонент исходного товара (например, если это заказ из netshop)
        'source_item_id' => null, // идентификатор исходного товара

        'paid_qty' => 0, // оплаченное количество; равно 0, qty; добавляется в nc_payment_invoice::get_items()

        // «виртуальные» свойства, см. self::get():
        //
        // 'total_price' => 0, // стоимость за все со скидкой
        // 'item_vat_amount' => 0, // сумма НДС за 1 единицу
        // 'total_vat_amount' => 0, // сумма НДС за все
        //
        // Значения со знаком (отрицательные для возврата):
        // 'signed_qty' => 1, // количество; отрицательное для возврата
        // 'signed_item_price' => 0, // цена за 1 единицу со скидкой; отрицательная для возврата
        // 'signed_total_price' => 0, // стоимость со скидкой; отрицательная для возврата
        // 'signed_item_vat_amount' => 0,
        // 'signed_total_vat_amount' => 0,

        // 'signed_paid_qty' => 0,
        // 'paid_amount' => 0,
        // 'signed_paid_amount' => 0,
    );

    protected $primary_key = 'id';
    protected $table_name = 'Payment_Invoice_Item';

    protected $mapping = array(
        'id' => 'Payment_Invoice_Item_ID',
        'invoice_id' => 'Payment_Invoice_ID',
        'operation' => 'Operation',
        'name' => 'Name',
        'item_price' => 'ItemPrice',
        'qty' => 'Qty',
        'vat_rate' => 'VatRate',
        'source_component_id' => 'Class_ID',
        'source_item_id' => 'Message_ID',
        // total_price, item_vat_amount, total_vat_amount, ... не сохраняются ни в БД,
        // ни в $properties (@see self::get())
    );

    /** @var array правила округления */
    static protected $properties_precision = array(
        'item_price' => 2,
        'qty' => 3,
    );

    /** @var array сохранять '' как NULL (а не 0) для следующих свойств: */
    static protected $nullable_properties = array(
        'vat_rate' => true,
        'source_component_id' => true,
        'source_item_id' => true,
    );

    /** @var array допустимые ставки НДС */
    static protected $allowed_vat_rates = array(null, 0, 10, 18);
    /** @var int ставка НДС, когда передано недопустимое значение */
    static protected $default_vat_rate = 18;

    /**
     *
     * @param string $property ключ в массиве $this->properties
     * @param mixed $value новое значение
     * @param boolean $add_new_property добавить свойство, если оно не было ранее определено
     * @throws nc_record_exception
     * @return $this
     */
    public function set($property, $value, $add_new_property = false) {
        if (isset(self::$properties_precision[$property])) {
            $value = round($value, self::$properties_precision[$property]);
        }

        if ($property === 'vat_rate') {
            if (!in_array($value, self::$allowed_vat_rates)) {
                $value = self::$default_vat_rate;
            }
        }

        if ($value == '' && isset(self::$nullable_properties[$property])) {
            $value = null;
        }

        return parent::set($property, $value, $add_new_property);
    }

    /**
     *
     * @param string $property ключ в массиве $this->properties
     * @return mixed значение
     * @throws nc_record_exception
     */
    public function get($property) {
        if (substr($property, 0, 7) === 'signed_') {
            $sign = ($this->get('operation') === nc_payment::OPERATION_SELL) ? 1 : -1;
            return $sign * $this->get(substr($property, 7));
        }

        switch ($property) {
            case 'total_price':
                return round($this->get('item_price') * $this->get('qty'), 2);

            case 'item_vat_amount':
                $item_price = $this->get('item_price');
                $tax = $item_price - $item_price / (1 + $this->get('vat_rate') / 100);
                return round($tax, 2);

            case 'total_vat_amount':
                $total_price = $this->get('total_price');
                $tax = $total_price - $total_price / (1 + $this->get('vat_rate') / 100);
                return round($tax, 2);

            case 'paid_amount':
                return round($this->get('item_price') * $this->get('paid_qty'), 2);

            default:
                return parent::get($property);
        }
    }

    /**
     * Сравнивает позицию счёта с другой позицией
     *
     * @see nc_payment_invoice_collection::get_items_balance()
     *
     * @param nc_payment_invoice_item $other_item
     * @return bool
     */
    public function is_same(nc_payment_invoice_item $other_item) {
        $properties = array('name', 'item_price', 'vat_rate', 'source_component_id', 'source_item_id');
        foreach ($properties as $property) {
            if ($this->get($property) != $other_item->get($property)) {
                return false;
            }
        }
        return true;
    }

}