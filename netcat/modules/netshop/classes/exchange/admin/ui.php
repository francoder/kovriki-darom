<?php

class nc_netshop_exchange_admin_ui extends nc_netshop_admin_ui {

    /**
     * @param $catalogue_id
     * @param string $current_action
     */
    public function __construct($catalogue_id, $current_action = "index") {
        parent::__construct('exchange', NETCAT_MODULE_NETSHOP_EXCHANGE);

        $this->catalogue_id = $catalogue_id;
    }

    /**
     * @param string $location  путь без 'module.netshop.exchange.'
     */
    public function set_exchange_location_suffix($location) {
        $this->locationHash = 'module.netshop.exchange' . (!empty($location) ? '' . $location : '');
    }
}