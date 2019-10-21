<?php


class ems_package_international_netshop_form extends nc_netshop_form {

    //--------------------------------------------------------------------------

    public $name    = NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL;
    public $keyword = 'ems_package_international';

    //--------------------------------------------------------------------------

    public function fields() {
        return array(
            'from_fullname'        => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_FULLNAME,
            'from_address_line1'   => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_ADDRESS_LINE1,
            'from_address_line2'   => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_ADDRESS_LINE2,
            'from_phone'           => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_PHONE,
            'to_fullname'          => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_FULLNAME,
            'to_address_line1'     => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_ADDRESS_LINE1,
            'to_address_line2'     => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_ADDRESS_LINE2,
            'to_phone'             => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_PHONE,
            'description'          => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_DESCRIPTION,
            'value'                => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_VALUE,
            'weight'               => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_WEIGHT
        );
    }

    //--------------------------------------------------------------------------
}