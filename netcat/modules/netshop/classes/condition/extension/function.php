<?php

class nc_netshop_condition_extension_function extends nc_netshop_condition {

    /**
     * Parameters:
     *    'function' — name of the function to call
     */

    protected $function;

    public function __construct($parameters = array()) {
        $this->function = trim(str_replace('()', '', $parameters['function']));
    }

    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        if ($this->validate_function_name()) {
            $function = $this->function;
            return (bool)$function($context, $current_item);
        }

        return false;
    }

    public function get_full_description(nc_netshop $netshop) {
        $description = $this->get_sanitized_function_name() . '() == true';
        
        if ($this->validate_function_name()) {
            return $description;
        }

        return $this->get_formatted_error_description($description);
    }

    public function get_short_description(nc_netshop $netshop) {
        return $this->get_full_description($netshop);
    }

    protected function validate_function_name() {
        if (!$this->function) {
            return false;
        }

        if (!function_exists($this->function)) {
            return false;
        }

        return true;
    }

    protected function get_sanitized_function_name() {
        return htmlspecialchars($this->function, ENT_QUOTES, 'UTF-8');
    }
}