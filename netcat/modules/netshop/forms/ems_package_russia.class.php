<?php


class ems_package_russia_netshop_form extends nc_netshop_form {

    //--------------------------------------------------------------------------

    public $name = NETCAT_MODULE_NETSHOP_EMS_RUSSIA;
    public $keyword = 'ems_package_russia';

    //--------------------------------------------------------------------------

    public function fields() {
        return array(
            'from_legal_entity'    => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_LEGAL_ENTITY,
            'from_fullname'        => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_FULLNAME,
            'from_phone'           => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_FROM_PHONE,
            'from_street'          => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_STREET,
            'from_house'           => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_HOUSE,

            'from_block'           => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_BLOCK,
            'from_floor'           => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_FLOOR,
            'from_apartment'       => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_APARTMENT,
            'from_intercom'        => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_INTERCOM,
            'from_city'            => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_CITY,
            'from_region'          => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_REGION,
            'from_zipcode'         => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_ZIPCODE,

            'to_legal_entity'      => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_TO_LEGAL_ENTITY,
            'to_fullname'          => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_FULLNAME,
            'to_phone'             => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_TO_PHONE,
            'to_street'            => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_STREET,
            'to_house'             => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_HOUSE,

            'to_block'             => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_BLOCK,
            'to_floor'             => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_FLOOR,
            'to_apartment'         => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_APARTMENT,
            'to_intercom'          => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_INTERCOM,
            'to_city'              => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_CITY,
            'to_region'            => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_REGION,
            'to_zipcode'           => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_FROM_ZIPCODE,

            'description'          => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_DESCRIPTION,
            'value'                => NETCAT_MODULE_NETSHOP_EMS_INTERNATIONAL_VALUE,
            'cash_on_delivery'     => NETCAT_MODULE_NETSHOP_EMS_RUSSIA_CASH_ON_DELIVERY
        );
    }

    //--------------------------------------------------------------------------
}