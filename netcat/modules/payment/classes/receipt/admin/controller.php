<?php

class nc_payment_receipt_admin_controller extends nc_payment_admin_controller {

    /** @var  nc_payment_receipt_admin_ui */
    protected $ui_config;
    protected $ui_config_class = 'nc_payment_receipt_admin_ui';

    /**
     *
     */
    protected function init() {
        parent::init();
        $this->bind('view', array('id'));
        $this->bind('resend_confirmation', array('id'));
    }


    /**
     * @param $id
     * @return nc_payment_receipt
     */
    protected function get_receipt($id) {
        try {
            return new nc_payment_receipt($id);
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

        $invoice_query =
            "SELECT SQL_CALC_FOUND_ROWS r.*
               FROM `Payment_Receipt` AS r
                    LEFT JOIN `Payment_Invoice` AS i USING (`Payment_Invoice_ID`)
              WHERE (i.`Catalogue_ID` = $site_id OR i.`Catalogue_ID` IS NULL)
              ORDER BY r.`Payment_Receipt_ID` DESC
              LIMIT $start, $per_page";

        $receipts = nc_record_collection::load('nc_payment_receipt', $invoice_query);

        $this->ui_config->set_location_hash("receipt($site_id)");

        if (!$receipts->count()) {
            return $this->view('empty');
        }

        return $this->view('list', array(
            'receipts' => $receipts,
            'total_pages' => ceil($receipts->get_total_count() / $per_page),
            'page' => $page,
            'page_url' => "?controller=" . $this->get_short_controller_name() . "&action=index&site=$site_id",
        ));
    }

    /**
     * @param $id
     * @return nc_ui_view
     */
    protected function action_view($id) {
        $id = (int)$id;
        $receipt = $this->get_receipt($id);

        $events = (array)nc_db()->get_results(
            "SELECT * FROM `Payment_RegisterLog` WHERE `Payment_Receipt_ID` = $id ORDER BY `RegisterLog_ID`",
            ARRAY_A
        );

        $this->ui_config->set_location_hash("receipt.view($id)");
        $this->ui_config->subheaderText = NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT . ' ' . $id;
        $this->ui_config->add_cancel_button();

        $this->ui_config->actionButtons[] = array(
            'id' => 'correction',
            'caption' => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_BUTTON,
            'location' => '#module.payment.invoice.correction(' . $receipt->get('invoice_id') . ')',
        );

        $can_resend_statuses = array(
            nc_payment_receipt::STATUS_CONNECTION_ERROR,
            nc_payment_receipt::STATUS_FAILED,
            nc_payment_receipt::STATUS_CANCELLED,
        );

        if (in_array($receipt->get('status'), $can_resend_statuses) && $receipt->get('register_provider_type') == nc_payment_receipt::PROVIDER_TYPE_REGISTER) {
            $this->ui_config->actionButtons[] = array(
                'id' => 'resend',
                'caption' => NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_RESEND_BUTTON,
                'location' => '#module.payment.receipt.resend(' . $receipt->get_id() . ')',
            );
        }

        return $this->view('details', array(
            'receipt' => $receipt,
            'events' => $events,
        ));
    }

    /**
     * @param $id
     * @return nc_ui_view
     */
    protected function action_resend_confirmation($id) {
        $id = (int)$id;
        $receipt = $this->get_receipt($id);

        $this->ui_config->set_location_hash("receipt.resend($id)");
        $this->ui_config->subheaderText = NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT . ' ' . $id;
        $this->ui_config->add_cancel_button();
        $this->ui_config->add_submit_button(NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_RESEND_BUTTON);

        return $this->view('resend', array(
            'receipt' => $receipt,
        ));
   }

    /**
     *
     */
   protected function action_resend() {
       $id = (int)$this->input->fetch_post('id');
       $receipt = $this->get_receipt($id);

       nc_payment_register::route_receipt_processing($receipt);
       $this->redirect_to_action('view', "&id=$id");
   }

}