<?php

class nc_payment_receipt_admin_helpers {

    /**
     * @param string $receipt_status
     * @return array [ short caption, color ]
     */
    static public function get_status_description_from_string($receipt_status) {
        $statuses = array(
            nc_payment_receipt::STATUS_NEW => array(
                NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_NEW,
                'light'
            ),

            nc_payment_receipt::STATUS_PENDING => array(
                NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_PENDING,
                'yellow'
            ),

            nc_payment_receipt::STATUS_REGISTERED => array(
                NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_REGISTERED,
                'green'
            ),

            nc_payment_receipt::STATUS_FAILED => array(
                NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_FAILED,
                'red'
            ),

            nc_payment_receipt::STATUS_CONNECTION_ERROR => array(
                NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_CONNECTION_ERROR,
                'orange'
            ),

            nc_payment_receipt::STATUS_CANCELLED => array(
                NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_CANCELLED,
                'olive'
            ),

            'default' => array(
                '???',
                '???',
                'grey'
            ),
        );

        return nc_array_value($statuses, $receipt_status, $statuses['default']);
    }

    /**
     * @param nc_payment_receipt $receipt
     * @return array [ short caption, color ]
     */
    static public function get_status_description(nc_payment_receipt $receipt) {
        return self::get_status_description_from_string($receipt->get('status'));
    }

    /**
     * @param nc_payment_receipt $receipt
     * @return string
     */
    static public function get_operation_string(nc_payment_receipt $receipt) {
        return $receipt->get('operation') === nc_payment::OPERATION_SELL
                    ? NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION_SELL
                    : NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION_SELL_REFUND;
    }

    /**
     * @param nc_payment_receipt $receipt
     * @return string
     */
    static public function get_register_provider_string(nc_payment_receipt $receipt) {
        $list = $receipt->get('register_provider_type') == nc_payment_receipt::PROVIDER_TYPE_SYSTEM
                    ? 'PaymentSystem'
                    : 'PaymentRegister';
        return nc_get_list_item_name($list, $receipt->get('register_provider_id'));
    }

    /**
     * @param $receipt_id
     * @return string
     */
    static public function get_receipt_link_by_id($receipt_id) {
        if (!$receipt_id) {
            return '';
        }
        $link = "#module.payment.receipt.view($receipt_id)";
        return nc_core::get_object()->ui->helper->hash_link($link, $receipt_id);
    }

}