<?php

class nc_payment_register_admin_ui extends nc_payment_admin_ui {

    /**
     *
     */
    public function __construct() {
        parent::__construct('register');
        $this->headerText = NETCAT_MODULE_PAYMENT_ADMIN_REGISTERS;

        $this->tabs = array(
            array(
                'id' => 'settings',
                'caption' => NETCAT_MODULE_PAYMENT_ADMIN_SETTINGS,
                'location' => 'module.payment.register',
                'group' => "admin"
            ),
            array(
                'id' => 'log',
                'caption' => NETCAT_MODULE_PAYMENT_ADMIN_LOG,
                'location' => 'module.payment.register.log',
                'group' => "admin",
            )
        );
    }

}