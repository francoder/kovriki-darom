<?php

class nc_payment_invoice_admin_helpers {

    /**
     * @param nc_payment_invoice $invoice
     * @return string
     */
    static public function get_customer_contacts_string(nc_payment_invoice $invoice) {
        $ui = nc_core::get_object()->ui;
        $result = '';

        if ($invoice->get('customer_id')) {
            $result .= $ui->helper->hash_link(
                'user.edit(' . $invoice->get('customer_id') . ')',
                $invoice->get('customer_name'),
                '',
                '_blank'
            );
        } else {
            $result .= htmlspecialchars($invoice->get('customer_name'));
        }

        $contact_data = array();
        $phone = $invoice->get('customer_phone');
        if ($phone) {
            $normalized_phone = nc_normalize_phone_number($phone);
            if ($normalized_phone) {
                $contact_data[] = '<a href="tel:' . $normalized_phone . '">' . $normalized_phone . '</a>';
            } else {
                $contact_data[] = self::mark_incorrect_contact_data($phone, NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_INCORRECT_PHONE);
            }
        }

        $email = $invoice->get('customer_email');
        if ($email) {
            if (nc_check_email($email)) {
                $contact_data[] = '<a href="mailto:' . $email . '">' . $email . '</a>';
            } else {
                $contact_data[] = self::mark_incorrect_contact_data($email, NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_INCORRECT_EMAIL);
            }
        }

        if ($contact_data) {
            $contact_data = join(', ', $contact_data);
            if ($result) {
                $result .= " ($contact_data)";
            } else {
                $result = $contact_data;
            }
        }

        return $result;
    }

    /**
     *
     */
    static protected function mark_incorrect_contact_data($data, $message) {
        return
            '<u class="nc-payment-admin-invoice-invalid-contact-data" title="' .
            htmlspecialchars($message . ': "' . $data . '"') .
            '">' .
            htmlspecialchars($data) .
            '</u>';
    }

    /**
     * @param nc_payment_invoice $invoice
     * @param int $site_id
     * @return string
     */
    static public function get_source_string(nc_payment_invoice $invoice, $site_id = null) {
        $ui = nc_core::get_object()->ui;

        $order_source = $invoice->get('order_source');
        $order_id = $invoice->get('order_id');

        if ($invoice->get('order_source') === 'netshop') {
            $order_site_id = $invoice->get('site_id') ?: $site_id;
            $order_link = "#module.netshop.order.view($order_site_id,$order_id)";
            return $ui->helper->hash_link($order_link, $order_id, '', '_blank');
        } else {
            $order_data = array_filter(array($order_source, $order_id));
            return join('-', $order_data);
        }
    }

    /**
     * @return array
     */
    static public function get_all_status_descriptions() {
        return array(
            nc_payment_invoice::STATUS_NEW => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_NEW,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_NEW_SHORT,
                'light'
            ),

            nc_payment_invoice::STATUS_SENT_TO_PAYMENT_SYSTEM => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SENT,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SENT_SHORT,
                'yellow'
            ),

            nc_payment_invoice::STATUS_CALLBACK_ERROR => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CALLBACK_ERROR,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_ERROR,
                'red'
            ),

            nc_payment_invoice::STATUS_CALLBACK_WRONG_SUM => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CALLBACK_WRONG_SUM,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_ERROR,
                'red'
            ),

            nc_payment_invoice::STATUS_WAITING => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_WAITING,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SENT_SHORT,
                'yellow'
            ),

            nc_payment_invoice::STATUS_SUCCESS => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SUCCESS,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SUCCESS,
                'green'
            ),

            nc_payment_invoice::STATUS_REJECTED => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_REJECTED,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_ERROR,
                'red'
            ),

            nc_payment_invoice::STATUS_CANCELLED => array(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CANCELLED,
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CANCELLED,
                'olive'
            ),
        );
    }

    /**
     * @param nc_payment_invoice $invoice
     * @return array [ full caption, short caption, color ]
     */
    static public function get_status_description(nc_payment_invoice $invoice) {
        $statuses = self::get_all_status_descriptions();
        return nc_array_value($statuses, $invoice->get('status'), array('???', '???', 'grey'));
    }

    /**
     * @param nc_payment_invoice_item $item
     * @return mixed|string
     */
    static public function get_item_link(nc_payment_invoice_item $item) {
        if ($item->get('source_component_id') && $item->get('source_item_id')) {
            return '<a target="_blank"' .
                   ' href="' . nc_object_url($item->get('source_component_id'), $item->get('source_item_id')) . '">' .
                   $item->get('name') .
                   '</a>';
        } else {
            return $item->get('name');
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     * @param int|null $current_invoice_id
     * @return string
     */
    static public function get_invoice_link(nc_payment_invoice $invoice, $current_invoice_id = null) {
        $id = $invoice->get_id();
        if ($id == $current_invoice_id) {
            return $id;
        }
        $link = "#module.payment.invoice.view($id)";
        return nc_core::get_object()->ui->helper->hash_link($link, $invoice->get_id());
    }

    /**
     * @param nc_payment_invoice_collection $invoices
     * @param int|null $current_invoice_id
     * @return string
     */
    static public function get_invoice_links(nc_payment_invoice_collection $invoices, $current_invoice_id = null) {
        $links = array();
        foreach ($invoices as $invoice) {
            $links[] = self::get_invoice_link($invoice, $current_invoice_id);
        }
        return join(', ', $links);
    }

}