<?php

class nc_payment_receipt_admin_ui extends nc_payment_admin_ui {

    /**
     *
     */
    public function __construct() {
        parent::__construct('receipt');
        $this->headerText = NETCAT_MODULE_PAYMENT_ADMIN_RECEIPTS;
    }

}