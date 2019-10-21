<?php

class nc_netshop_condition_item_property extends nc_netshop_condition {

    /**
     * Parameters:
     *   field:  "ClassID:FieldName:FieldTypeID". ClassID = "*" if applies to multiple components
     *   op
     *   value
     */

    protected $field;
    protected $op;
    protected $value;

    protected $component_id;
    protected $field_name;
    protected $field_type;

    public function __construct($parameters = array()) {
        $this->field = $parameters['field'];
        $this->op = $parameters['op'];
        $this->value = $this->convert_decimal_point($parameters['value']);

        // assuming all components of the 'field' parameter must be set
        list($component_id, $field_name, $field_type) = explode(":", $parameters['field']);
        $this->component_id = $component_id;
        $this->field_name = $field_name;
        $this->field_type = $field_type;
    }


    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        if (!$current_item instanceof nc_netshop_item) { return false; }

        if ($this->component_id == "*" || $this->component_id == $current_item['Class_ID']) { // same component as specified in conditions
            // does the item component have a field with this name and type?
            if (nc_core::get_object()->get_component($current_item['Class_ID'])->has_field($this->field_name, $this->field_type)) {
                // ok, now we can compare the values
                if ($this->field_type == NC_FIELDTYPE_SELECT) {
                    $item_field_value = $current_item[$this->field_name . "_id"];
                }
                elseif ($this->field_type == NC_FIELDTYPE_MULTISELECT) {
                    $item_field_value = join(',', $current_item[$this->field_name . "_id"]);
                }
                else {
                    $item_field_value = $current_item[$this->field_name];
                }

                return $this->compare($item_field_value, $this->op, $this->value, $this->field_type);
            }
        }
        return false;
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
        $new_component_id = $dumper->get_dict('Class_ID', $this->component_id);
        return array('field' => "{$new_component_id}:{$this->field_name}:{$this->field_type}",
                     'op' => $this->op,
                     'value' => $this->value);
    }

}