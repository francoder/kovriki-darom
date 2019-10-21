<?php

class nc_netshop_condition_orders_item extends nc_netshop_condition {

    /**
     * Parameters:
     *   value:  "ComponentID:ItemID"
     */

    protected $component_id;
    protected $item_id;

    public function __construct($parameters = array()) {
        $value = explode(":", $parameters['value']);
        $this->component_id = $value[0];
        $this->item_id = $value[1];
    }


    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        return $context->previous_orders_had_item($this->component_id, $this->item_id);
    }

    /**
     * Короткое описание (только значение, для повторяющихся условий)
     * @param nc_netshop $netshop
     * @return string
     */
    public function get_short_description(nc_netshop $netshop) {
        $item = nc_netshop_admin_helpers::get_item($this->component_id, $this->item_id);
        if (!$item['Sub_Class_ID']) {
            return "<em class='nc--status-error'>" . NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_ITEM . "</em>";
        }

        return sprintf(
            NETCAT_MODULE_NETSHOP_COND_QUOTED_VALUE,
            $this->add_operator_description("<a target='_blank' href='$item[URL]'>$item[FullName]</a>")
        );
    }

    /**
     * @param nc_backup_dumper $dumper
     * @return array
     */
    public function get_updated_parameters_on_import(nc_backup_dumper $dumper) {
        $new_component_id = $dumper->get_dict('Class_ID', $this->component_id);
        $new_item_id = $dumper->get_dict("Message{$new_component_id}.Message_ID", $this->item_id);
        return array('value' => $new_component_id . ":" . $new_item_id);
    }

}