<?php

class nc_netshop_condition_order_property extends nc_netshop_condition {

    /**
     * Parameters:
     *    'field'
     *    'op'
     *    'value'
     */

    protected $field;
    protected $op;
    protected $value;


    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        $order_field_value = $context->get_order_property($this->field);
        $field_type = $context->get_order_field_type($this->field);
        if ($field_type == NC_FIELDTYPE_MULTISELECT && is_array($order_field_value)) {
            $order_field_value = join(',', $order_field_value);
        }
        return $this->compare($order_field_value, $this->op, $this->value, $field_type);
    }

    /**
     * Короткое описание (только значение, для повторяющихся условий)
     * @param nc_netshop $netshop
     * @return string
     */
    public function get_short_description(nc_netshop $netshop) {
        $order_component = new nc_component($netshop->get_setting('OrderComponentID'));
        $field_data = $order_component->get_field($this->field);

        if (!$field_data) { return "<em class='nc--status-error'>" . NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_FIELD . "</em>"; } // what?!

        $value = $this->value;
        $op = $this->op;
        if ($op == 'eq') { $op = 'EQ_IS'; }

        switch ($field_data['type']) {
            case NC_FIELDTYPE_SELECT:
            case NC_FIELDTYPE_MULTISELECT:
                $value = nc_get_list_item_name($field_data['table'], $this->value);
                if ($value === null) { $value = "<em class='nc--status-error'>" . NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_VALUE . "</em>"; }
                break;
            case NC_FIELDTYPE_DATETIME:
                $value = nc_netshop_condition_admin_helpers::format_date($this->value);
                $op = $this->op . "_DATE";
                break;
            case NC_FIELDTYPE_BOOLEAN:
                $value = $value ? NETCAT_MODULE_NETSHOP_COND_BOOLEAN_TRUE
                                : NETCAT_MODULE_NETSHOP_COND_BOOLEAN_FALSE;
        }

        return nc_lcfirst($field_data['description']) . ' ' .
               $this->add_operator_description($value, $op);
    }

}