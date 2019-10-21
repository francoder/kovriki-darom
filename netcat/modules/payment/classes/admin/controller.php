<?php

abstract class nc_payment_admin_controller extends nc_ui_controller {
    
    protected $use_layout = true;

    /** @var  nc_payment_admin_ui */
    protected $ui_config;

    /** @var string  Должен быть задан, или должен быть переопределён метод before_action() */
    protected $ui_config_class = 'nc_payment_admin_ui';

    /**
     *
     */
    protected function before_action() {
        /** @var Permission $perm */
        global $perm;
        if (!$perm->isSupervisor()) {
            die(NETCAT_MODERATION_ERROR_NORIGHT);
        }

        if ($this->ui_config_class) {
            $ui_config_class = $this->ui_config_class;
            $this->ui_config = new $ui_config_class();
        }
    }

    /**
     * @param $result
     * @return string
     */
    protected function after_action($result) {
        if (!$this->use_layout) {
            return $result;
        }

        BeginHtml(NETCAT_MODULE_PAYMENT_NAME, '', '');
        echo '<div class="nc-payment-admin">', $result, '</div>';
        EndHtml();
        return '';
    }

    /**
     * @param nc_ui_view $view
     * @return nc_ui_view
     */
    protected function init_view(nc_ui_view $view) {
        return $view->with('site_id', $this->site_id)
                    ->with('short_controller_name', $this->get_short_controller_name());
    }

    /**
     * @return string
     */
    protected function get_short_controller_name() {
        preg_match("/^nc_payment_(.+)_admin_controller$/", get_class($this), $matches);
        if ($matches) {
            return $matches[1];
        }
        die ('Non-standard controller class name; please override ' . __METHOD__ . '() or methods that use it');
    }

    /**
     * @return string
     */
    protected function get_script_path() {
        return nc_module_path('payment') . 'admin/?controller=' . $this->get_short_controller_name() . '&action=';
    }

    /**
     * @param string $action
     * @param string $params
     */
    protected function redirect_to_action($action = 'index', $params = '') {
        $location = $this->get_script_path() . $action .
                    '&site_id=' . (int)$this->site_id .
                    ($params[0] == '&' ? $params : "&$params");

        ob_clean();
        header("Location: $location");
        die;
    }

}