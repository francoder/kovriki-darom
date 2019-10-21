<?php

class nc_payment {

    /** тип операции «возврат прихода» */
    const OPERATION_SELL_REFUND = 'sell_refund';
    /** тип операции «приход» */
    const OPERATION_SELL = 'sell';

    /**
     *
     */
    static public function init() {
        nc_core::get_object()->register_class_autoload_path("nc_payment_", __DIR__ . "/classes");
        self::register_system_events();
        new nc_payment_logger();
    }

    /**
     *
     */
    static protected function register_system_events() {
        $event_manager = nc_core::get_object()->event;

        $events = array(
            "EVENT_ON_INIT",
            "EVENT_BEFORE_PAY_REQUEST",
            "EVENT_AFTER_PAY_REQUEST",
            "EVENT_ON_PAY_REQUEST_ERROR",
            "EVENT_BEFORE_PAY_CALLBACK",
            "EVENT_AFTER_PAY_CALLBACK",
            "EVENT_ON_PAY_CALLBACK_ERROR",
            "EVENT_ON_PAY_SUCCESS",
            "EVENT_ON_PAY_FAILURE",
            "EVENT_ON_PAY_REJECTED"
        );

        foreach ($events as $event_constant) {
            $event_name = constant("nc_payment_system::" . $event_constant);
            $event_description = constant("NETCAT_MODULE_PAYMENT_" . $event_constant);
            $event_manager->register_event($event_name, $event_description, true);
        }

    }

    /**
     * Changes invoice status
     * and triggers events
     *
     * @param $bill_id
     * @param $paid
     */
    static public function change_invoice_status_by_bill_id($bill_id, $paid) {
        $bill_id = (int)$bill_id;
        $sql = "SELECT * FROM %t% WHERE `Order_Source` = 'bills' AND `Order_ID` = {$bill_id}";
        $invoices = nc_record_collection::load('nc_payment_invoice', $sql);

        foreach ($invoices as $invoice) {
            $invoice->set('status', $paid ? nc_payment_invoice::STATUS_SUCCESS : nc_payment_invoice::STATUS_NEW);
            $invoice->save();
            nc_core('event')->execute($paid ? nc_payment_system::EVENT_ON_PAY_SUCCESS : nc_payment_system::EVENT_ON_PAY_FAILURE, nc_payment_factory::create('nc_payment_system_bank'), $invoice);
        }
    }

    /**
     * Returns juridical bill
     * payment system id
     *
     * @return int
     */
    static public function get_juridical_bill_payment_system_id() {
        return self::get_payment_system_id_by_name('nc_payment_system_bank');
    }

    /**
     * Returns physical bill
     * payment system id
     *
     * @return int
     */
    static public function get_physical_bill_payment_system_id() {
        return self::get_payment_system_id_by_name('nc_payment_system_sberbank');
    }

    /**
     * Returns payment system id
     * by name
     *
     * @return int
     */
    static protected function get_payment_system_id_by_name($name) {
        $db = nc_core('db');
        $name = $db->escape($name);
        $sql = "SELECT `PaymentSystem_ID` FROM `Classificator_PaymentSystem` WHERE `Value` = '{$name}'";

        return (int)$db->get_var($sql);
    }

    /**
     * @param nc_payment_invoice $invoice
     * @param bool $include_cancelled
     * @return nc_payment_invoice_collection
     */
    static public function load_order_invoices_by_invoice(nc_payment_invoice $invoice, $include_cancelled = false) {
        $db = nc_db();

        $order_source = $db->escape($invoice->get('order_source'));
        $order_id = $db->escape($invoice->get('order_id'));

        if ($order_source && $order_id) {
            $site_id = (int)$invoice->get('site_id');
            $related_invoices_query =
                "SELECT *
                   FROM `Payment_Invoice`
                  WHERE (`Catalogue_ID` = $site_id OR `Catalogue_ID` IS NULL)
                    AND `Order_Source` = '$order_source'
                    AND `Order_ID` = '$order_id'
                    AND `Payment_Invoice_ID` != " . (int)$invoice->get_id() . "
                    " . ($include_cancelled ? "" : " AND `Status` != " . nc_payment_invoice::STATUS_CANCELLED) . "
                  ORDER BY `Payment_Invoice_ID`";

            $all_order_invoices = nc_payment_invoice_collection::load_records($related_invoices_query);
            if ($invoice->get('status') != nc_payment_invoice::STATUS_CANCELLED || $include_cancelled) {
                $all_order_invoices = $all_order_invoices->add($invoice)->sort_by_property_value('id');
            }
        } else {
            $all_order_invoices = new nc_payment_invoice_collection(array($invoice));
        }

        return $all_order_invoices;
    }

    /**
     * @param $site_id
     * @param $order_source
     * @param $order_id
     * @param bool $include_cancelled
     * @return nc_payment_invoice_collection
     */
    static public function load_order_invoices($site_id, $order_source, $order_id, $include_cancelled = false) {
        $db = nc_db();
        $site_id = (int)$site_id;
        $order_source = $db->escape($order_source);
        $order_id = $db->escape($order_id);

        $invoices_query =
            "SELECT *
               FROM `Payment_Invoice`
              WHERE (`Catalogue_ID` = $site_id OR `Catalogue_ID` IS NULL)
                AND `Order_Source` = '$order_source'
                AND `Order_ID` = '$order_id'
                " . ($include_cancelled ? "" : " AND `Status` != " . nc_payment_invoice::STATUS_CANCELLED) . "
              ORDER BY `Payment_Invoice_ID`";

        return nc_payment_invoice_collection::load_records($invoices_query);
    }

    /**
     * @param $setting
     * @param null $site_id
     * @return mixed
     */
    static public function get_setting($setting, $site_id = null) {
        return nc_core::get_object()->get_settings($setting, 'payment', false, $site_id);
    }

}