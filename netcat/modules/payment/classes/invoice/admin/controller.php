<?php

class nc_payment_invoice_admin_controller extends nc_payment_admin_controller {

    /** @var  nc_payment_invoice_admin_ui */
    protected $ui_config;
    protected $ui_config_class = 'nc_payment_invoice_admin_ui';

    /**
     *
     */
    protected function init() {
        parent::init();
        $this->bind('view', array('id'));
        $this->bind('correction', array('id'));
    }


    /**
     * @param $id
     * @return nc_payment_invoice
     */
    protected function get_invoice($id) {
        try {
            return new nc_payment_invoice($id);
        } catch (Exception $e) {
            $this->redirect_to_action('index');
            die;
        }
    }

    /**
     * Отображение списка счетов
     */
    protected function action_index() {
        $site_id = (int)$this->site_id;
        $page = (int)$this->input->fetch_get('page') ?: 1;
        $per_page = 100;
        $start = ($page - 1) * $per_page;

        $search = trim($this->input->fetch_get('search'));
        $search_query = $this->get_search_sql_conditions($search);

        $invoice_query =
            "SELECT SQL_CALC_FOUND_ROWS *
               FROM `Payment_Invoice`
              WHERE (`Catalogue_ID` = $site_id OR `Catalogue_ID` IS NULL)
                    $search_query
              ORDER BY `Payment_Invoice_ID` DESC
              LIMIT $start, $per_page";

        $invoices = nc_record_collection::load('nc_payment_invoice', $invoice_query);

        $this->ui_config->set_location_hash("invoice($site_id)");

        if (!$invoices->count() && !$search_query) {
            return $this->view('empty');
        }

        return $this->view('list', array(
            'invoices' => $invoices,
            'total_pages' => ceil($invoices->get_total_count() / $per_page),
            'page' => $page,
            'page_url' => "?controller=" . $this->get_short_controller_name() . "&action=index&site=$site_id",
            'search' => $search,
        ));
    }

    /**
     * @param $search_string
     * @return array|string
     */
    protected function get_search_sql_conditions($search_string) {
        if (!strlen($search_string)) {
            return '';
        }

        $search_string = nc_db()->escape($search_string);
        $search_query = array();

        foreach (array('Payment_Invoice_ID', 'Order_ID') as $field) {
            if (is_numeric($search_string)) {
                $search_query[] = "`$field` = '$search_string'";
            }
        }

        $search_query[] = "`Customer_Name` LIKE '%$search_string%'";

        if (strpos($search_string, '@')) {
            $search_query[] = "`Customer_Email` = '$search_string'";
        }

        // поиск по номеру телефона, если в строке поиска более 5 цифр
        preg_match_all('/\d/', $search_string, $matches);
        if (count($matches[0]) > 5) {
            $phone_regexp = join('[^0-9]*', $matches[0]);
            $search_query[] = "`Customer_Phone` REGEXP '$phone_regexp'";
        }

        $search_query = ' AND (' . join(' OR ', $search_query) . ')';

        return $search_query;
    }

    /**
     * @param $id
     * @return nc_ui_view
     */
    protected function action_view($id) {
        $id = (int)$id;
        $invoice = $this->get_invoice($id);

        $nc_core = nc_core::get_object();

        $site_id = $invoice->get('site_id') ?: $this->site_id;
        $site_payment_systems = $nc_core->db->get_col(
            "SELECT c.PaymentSystem_ID, c.PaymentSystem_Name
              FROM `Classificator_PaymentSystem` AS c
                   JOIN `Payment_SystemCatalogue` AS s 
                     ON (
                            s.`Catalogue_ID` = $site_id
                            AND s.`PaymentSystem_ID` = c.`PaymentSystem_ID`
                            AND s.`Checked` = 1
                        )
            ORDER BY c.`PaymentSystem_Priority`",
            1, 0
        );

        $show_payment_link =
            $invoice->get('amount') > 0 &&
            !in_array($invoice->get('status'), array(
                nc_payment_invoice::STATUS_SUCCESS,
                nc_payment_invoice::STATUS_REJECTED,
                nc_payment_invoice::STATUS_CANCELLED,
            ));

        $payment_script_url = $nc_core->catalogue->get_url_by_id($site_id) . nc_module_path('payment') . 'pay_request.php';
        $are_receipts_enabled = nc_payment_register::is_enabled($site_id);

        $this->ui_config->set_location_hash("invoice.view($id)");
        $this->ui_config->subheaderText = NETCAT_MODULE_PAYMENT_ADMIN_INVOICE . ' ' . $id;
        $this->ui_config->add_cancel_button();

        if ($invoice->get('status') != nc_payment_invoice::STATUS_CANCELLED) {
            $this->ui_config->actionButtons[] = array(
                'id' => 'correction',
                'caption' => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_BUTTON,
                'align' => 'right',
                'location' => "#module.payment.invoice.correction($id)"
            );
        }

        return $this->view('details', array(
            'invoice' => $invoice,
            'all_order_invoices' => nc_payment::load_order_invoices_by_invoice($invoice, true),
            'site_payment_systems' => $site_payment_systems,
            'show_payment_link' => $show_payment_link,
            'payment_script_url' => $payment_script_url,
            'are_receipts_enabled' => $are_receipts_enabled,
        ));
    }

    /**
     * @param $id
     * @return nc_ui_view
     */
    protected function action_correction($id) {
        $id = (int)$id;

        $invoice = $this->get_invoice($id);

        $this->ui_config->set_location_hash("invoice.correction($id)");
        $this->ui_config->subheaderText = sprintf(
            NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUB_HEADER,
            nc_payment_invoice_admin_helpers::get_source_string($invoice)
        );
        $this->ui_config->add_submit_button(NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_CREATE_BUTTON);
        $this->ui_config->add_cancel_button();

        return $this->view('correction', array(
            'id' => $id,
            'order_source' => $invoice,
            'all_order_invoices' => nc_payment::load_order_invoices_by_invoice($invoice),
            'error' => null,
        ));
    }

    /**
     *
     */
    protected function action_create_correction_invoice() {
        $primary_invoice_id = (int)$this->input->fetch_post('primary_invoice_id');
        $primary_invoice = $this->get_invoice($primary_invoice_id);

        $corrected_invoices = nc_payment::load_order_invoices_by_invoice($primary_invoice);
        $unpaid_invoices = $corrected_invoices->where('status', nc_payment_invoice::STATUS_SUCCESS, '!=');

        $corrected_items = $this->input->fetch_post('existing');
        $new_items = $this->input->fetch_post('new');

        // проход 1: смотрим, изменилось ли что-то в форме
        $has_corrections = false;
        foreach ($corrected_items as $item_data) {
            if ($item_data['new_qty'] != $item_data['qty']) {
                $has_corrections = true;
                break;
            }
        }
        if (!$has_corrections && !$new_items) {
            return $this->action_correction($primary_invoice_id)->with('error', NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_NO_CHANGES);
        }

        // проход 2: собираем данные для нового корректировочного счёта
        $correction_invoice_items = new nc_payment_invoice_item_collection();
        $full_refund_with_zero_amount = true;
        foreach ($corrected_items as $item_data) {
            $qty_change = $item_data['new_qty'] - $item_data['paid_qty'];
            if ($qty_change != 0) {
                $full_refund_with_zero_amount =
                    $full_refund_with_zero_amount &&
                    $item_data['paid_qty'] == 0 && $item_data['new_qty'] == 0;
                $item_data['operation'] = $qty_change > 0 ? nc_payment::OPERATION_SELL : nc_payment::OPERATION_SELL_REFUND;
                $item_data['qty'] = abs($qty_change);
                unset($item_data['new_qty'], $item_data['id'], $item_data['invoice_id']);
                $correction_invoice_items->add(new nc_payment_invoice_item($item_data));
            }
        }

        foreach ((array)$new_items as $item_data) {
            $correction_invoice_items->add(new nc_payment_invoice_item($item_data));
        }

        // cancel unpaid invoices
        /** @var nc_payment_invoice $invoice */
        foreach ($unpaid_invoices as $invoice) {
            $invoice->set('status', nc_payment_invoice::STATUS_CANCELLED)->save();
        }

        if ($full_refund_with_zero_amount && !$new_items) {
            // не создаём новый счёт, если произведён «возврат» по неоплаченному счёту
            return $this->redirect_to_action('view', '&id=' . $primary_invoice_id);
        }

        $correction_amount = $correction_invoice_items->sum('signed_total_price');

        $correction_invoice_data = array_merge(
            $primary_invoice->to_array(),
            array(
                'id' => null,
                'type' => nc_payment_invoice::TYPE_CORRECTION,
                'amount' => $correction_amount,
                'status' => nc_payment_invoice::STATUS_NEW,
                'last_response' => '',
                'created' => null,
            )
        );

        $correction_invoice = new nc_payment_invoice($correction_invoice_data);
        $correction_invoice->set_items($correction_invoice_items)->save();

        return $this->redirect_to_action('view', '&id=' . $correction_invoice->get_id());
    }

    /**
     *
     */
    protected function action_change_status() {
        $invoice_id = (int)$this->input->fetch_post('id');
        $status = $this->input->fetch_post('new_status');

        $invoice = $this->get_invoice($invoice_id);
        $invoice->set('status', $status)->save();

        if ($status == nc_payment_invoice::STATUS_SUCCESS) {
            $payment_system = nc_payment_factory::create($invoice->get('payment_system_id'));
            if ($payment_system instanceof nc_payment_system) {
                $payment_system->process_manual_invoice_status_change($invoice);
            }
        }

        $this->redirect_to_action('view', '&id=' . $invoice_id);
    }

}