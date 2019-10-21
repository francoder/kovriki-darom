<?php

class nc_netshop_condition_cart_itemproperty extends nc_netshop_condition {

    /**
     * Количество товаров с указанными свойствами компонента в корзине
     *
     * Названия параметров отличаются от свойств объекта.
     * Параметры:
     *  - qty    — количество товара
     *  - qty_op — оператор сравнения количества
     *  - field  — искомое свойство у товаров
     *  - op     — оператор сравнения значения поля
     *  - value  — значение поля
     */

    protected $qty;
    protected $qty_op;

    protected $component_id;
    protected $field_name;
    protected $field_op;
    protected $field_type;
    protected $field_value;


    public function __construct($parameters = array()) {
        $this->qty = $parameters['qty'];
        $this->qty_op = $parameters['qty_op'];
        $this->field_op = $parameters['op'];
        $this->field_value = $parameters['value'];

        // assuming that all components of the 'field' parameter must be set
        list($component_id, $field_name, $field_type) = explode(":", $parameters['field']);
        $this->component_id = $component_id;
        $this->field_name = $field_name;
        $this->field_type = $field_type;
    }


    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        $total_qty = 0;
        $nc_core = nc_core::get_object();

        foreach ($context->get_cart_contents() as $item) {
            if ($this->component_id == "*" || $this->component_id == $item['Class_ID']) { // component match conditions
                // does the item component have a field with this name and type?
                if (!$nc_core->get_component($item['Class_ID'])->has_field($this->field_name, $this->field_type)) {
                    continue;
                }

                if ($this->field_type == NC_FIELDTYPE_SELECT) {
                    $item_field_value = $item[$this->field_name . '_id'];
                }
                else if ($this->field_type == NC_FIELDTYPE_MULTISELECT) {
                    $item_field_value = join(',', $item[$this->field_name . '_id']);
                }
                else {
                    $item_field_value = $item[$this->field_name];
                }

                if ($this->compare($item_field_value, $this->field_op, $this->field_value, $this->field_type)) {
                    $total_qty += $item['Qty'];
                }
            }
        }

        return $this->compare($total_qty, $this->qty_op, $this->qty);
    }

    /**
     * Короткое описание (только значение, для повторяющихся условий)
     * @param nc_netshop $netshop
     * @return string
     */
    public function get_short_description(nc_netshop $netshop) {
        $field_data = nc_netshop_condition_admin_helpers::get_field_data(
            $this->component_id,
            $this->field_name,
            $this->field_type,
            $netshop);

        if (!$field_data) { return "<em class='nc--status-error'>" . NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_FIELD . "</em>"; } // what?!

        $field_value = $this->field_value;
        $field_op = $this->field_op;
        if ($field_op == 'eq') { $field_op = 'EQ_IS'; }

        switch ($field_data['type']) {
            case NC_FIELDTYPE_SELECT:
            case NC_FIELDTYPE_MULTISELECT:
                $field_value = nc_get_list_item_name($field_data['table'], $field_value);
                if ($field_value === null) { $field_value = "<em class='nc--status-error'>" . NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_VALUE . "</em>"; }
                break;
            case NC_FIELDTYPE_DATETIME:
                $field_value = nc_netshop_condition_admin_helpers::format_date($field_value);
                $field_op = $this->field_op . "_DATE";
                break;
            case NC_FIELDTYPE_BOOLEAN:
                $field_value = $field_value ? NETCAT_MODULE_NETSHOP_COND_BOOLEAN_TRUE
                                            : NETCAT_MODULE_NETSHOP_COND_BOOLEAN_FALSE;
        }

        return $this->add_operator_description($this->qty, $this->qty_op) . ' ' .
               NETCAT_MODULE_NETSHOP_COND_ORDERS_ITEM_UNITS .
               NETCAT_MODULE_NETSHOP_COND_CART_ITEMPROPERTY_WITH . ' ' .
               nc_lcfirst($field_data['description']) . ' ' .
               $this->add_operator_description($field_value, $field_op);
    }

    /**
     * @param nc_backup_dumper $dumper
     * @return array
     */
    protected function get_updated_parameters_on_import(nc_backup_dumper $dumper) {
        $new_component_id = $dumper->get_dict('Class_ID', $this->component_id);
        return array('qty_op' => $this->qty_op,
                     'qty' => $this->qty,
                     'field' => "{$new_component_id}:{$this->field_name}:{$this->field_type}",
                     'op' => $this->field_op,
                     'value' => $this->field_value);
    }

}