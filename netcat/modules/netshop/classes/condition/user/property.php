<?php

class nc_netshop_condition_user_property extends nc_netshop_condition {

    /**
     * Parameters:
     *    'field'
     *    'op'
     *    'value'
     */

    protected $field_type;
    protected $field_name;
    protected $op;
    protected $value;


    public function __construct($parameters = array()) {
        $this->op = $parameters['op'];
        $this->value = $this->convert_decimal_point($parameters['value']);

        $this->field_name = $parameters['field'];
        $this->field_type = nc_core::get_object()->get_component('User')->get_field($this->field_name, 'type');
    }


    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        if ($this->field_type == NC_FIELDTYPE_SELECT) {
            $user_field_value = $context->get_user_property($this->field_name . '_id');
        }
        else if ($this->field_type == NC_FIELDTYPE_MULTISELECT) {
            $user_field_value = join(',', $context->get_user_property($this->field_name . '_id'));
        }
        else {
            $user_field_value = $context->get_user_property($this->field_name);
        }

        return $this->compare($user_field_value, $this->op, $this->value, $this->field_type);
    }

    /**
     * Короткое описание (только значение, для повторяющихся условий)
     * @param nc_netshop $netshop
     * @return string
     */
    public function get_short_description(nc_netshop $netshop) {
        $order_component = new nc_Component('User');
        $field_data = $order_component->get_field($this->field_name);

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

    /**
     * @param nc_backup_dumper $dumper
     * @return array
     */
    public function get_updated_parameters_on_import(nc_backup_dumper $dumper) {
        return array('field' => $this->field_name,
                     'op' => $this->op,
                     'value' => $this->value);
    }

}