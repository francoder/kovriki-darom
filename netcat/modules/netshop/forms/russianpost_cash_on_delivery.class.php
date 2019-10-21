<?php


class russianpost_cash_on_delivery_netshop_form extends nc_netshop_form {

    //--------------------------------------------------------------------------

    public $name = NETCAT_MODULE_NETSHOP_RUSSIANPOST_CASH_ON_DELIVERY;
    public $keyword = 'russianpost_cash_on_delivery';

    //--------------------------------------------------------------------------

    public function fields() {
        return array(
            'from_fullname'        => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_FULLNAME,
            'from_address_line1'   => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_ADDRESS_LINE1,
            'from_address_line2'   => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_ADDRESS_LINE2,
            'from_zipcode'         => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_ZIPCODE,
            'from_inn'             => NETCAT_MODULE_NETSHOP_POST_INN,

            'to_fullname'          => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_FULLNAME,
            'to_address_line1'     => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_ADDRESS_LINE1,
            'to_address_line2'     => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_ADDRESS_LINE2,
            'to_zipcode'           => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_ZIPCODE,

            'receiver_inn'         => NETCAT_MODULE_NETSHOP_POST_INN,
            'receiver_corr'        => NETCAT_MODULE_NETSHOP_POST_KOR,
            'receiver_bank'        => NETCAT_MODULE_NETSHOP_POST_BANK,
            'receiver_account'     => NETCAT_MODULE_NETSHOP_POST_ACCOUNT,
            'receiver_bik'         => NETCAT_MODULE_NETSHOP_POST_BIK,

            'value'                => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_VALUE,
            'cash_on_delivery'     => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_CASH_ON_DELIVERY,
            'weight'               => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_WEIGHT
        );
    }

    //--------------------------------------------------------------------------
}