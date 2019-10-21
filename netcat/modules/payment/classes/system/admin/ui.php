<?php

    class nc_payment_system_admin_ui extends nc_payment_admin_ui {
    
        /**
         *
         */
        public function __construct() {
            parent::__construct('system');
            $this->headerText = NETCAT_MODULE_PAYMENT_ADMIN_PAYMENT_SYSTEMS;
        }

    }