<?php

class nc_netshop_condition_cart_propertymin extends nc_netshop_condition {

    /**
     * Parameters:
     *   field
     *   op
     *   value
     */
    protected $op;
    protected $value;

    protected $component_id;
    protected $field_name;
    protected $field_type;

    public function __construct($parameters = array()) {
        // assuming all components of the 'field' parameter must be set
        list($component_id, $field_name, $field_type) = explode(":", $parameters['field']);
        $this->component_id = $component_id;
        $this->field_name = $field_name;
        $this->field_type = $field_type;

        $this->op = $parameters['op'];
        $this->value = $this->convert_decimal_point($parameters['value']);
    }


    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        $min_value = null;
        $items = $context->get_cart_contents();
        foreach ($items as $item) {
            if ($this->component_id == '*' || $item['Class_ID'] == $this->component_id) {
                if ($min_value == null) {
                    $min_value = $item[$this->field_name];
                }
                else {
                    $min_value = min($min_value, $item[$this->field_name]);
                }
            }
        }
        return $this->compare($min_value, $this->op, $this->value);
    }


    public function get_full_description(nc_netshop $netshop) {
        $field_data = nc_netshop_condition_admin_helpers::get_field_data(
            $this->component_id,
            $this->field_name,
            $this->field_type,
            $netshop);

        if (!$field_data) {
            return $this->get_formatted_error_description(NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_FIELD);
        }

        return sprintf(NETCAT_MODULE_NETSHOP_COND_CART_PROPERTYMIN, $field_data['description']) . ' ' .
               $this->get_short_description($netshop);
    }

    /**
     * @param nc_backup_dumper $dumper
     * @return array
     */
    public function get_updated_parameters_on_import(nc_backup_dumper $dumper) {
        $new_component_id = $dumper->get_dict('Class_ID', $this->component_id);
        return array('field' => "{$new_component_id}:{$this->field_name}:{$this->field_type}",
                     'op' => $this->op,
                     'value' => $this->value);
    }

}