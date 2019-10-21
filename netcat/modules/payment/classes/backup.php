<?php

/**
 *
 */
class nc_payment_backup extends nc_backup_extension {

    protected $invoice_import_source;
    protected $invoice_import_order_ids;

    /**
     * @param string $type
     * @param int $id
     */
    public function export($type, $id) {
        if ($type != 'site') { return; }

        $data = nc_db_table::make('Payment_SystemSetting')->where('Catalogue_ID', $id)->get_result();
        $this->dumper->export_data('Payment_SystemSetting', 'Param_ID', $data);

        $data = nc_db_table::make('Payment_SystemCatalogue')->where('Catalogue_ID', $id)->get_result();
        $this->dumper->export_data('Payment_SystemCatalogue', 'PaymentSystem_ID', $data);

        if ($data) {
            $this->export_classificator('PaymentSystem');
        }
    }

    /**
     * @param string $type
     * @param int $id
     */
    public function import($type, $id) {
        if ($type != 'site') { return; }

        $this->dumper->import_data('Payment_SystemSetting', null, array('System_ID' => 'Classificator_PaymentSystem.PaymentSystem_ID'));
        $this->dumper->import_data('Payment_SystemCatalogue');
    }


    /**
     * @param string $source
     * @param array $ids
     */
    public function export_invoices($source, array $ids) {
        $invoices = nc_db_table::make('Payment_Invoice')
                        ->where('Order_Source', $source)
                        ->where_in('Order_ID', $ids)
                        ->index_by('Payment_Invoice_ID')
                        ->get_result();

        $this->dumper->export_data('Payment_Invoice', 'Payment_Invoice_ID', $invoices);

        $payment_log = nc_db_table::make('Payment_Log')->where_in('Payment_Invoice_ID', array_keys($invoices))->get_result();
        $this->dumper->export_data('Payment_Log', 'Log_ID', $payment_log);
    }

    /**
     * @param $source
     * @param array $id_map
     * @throws Exception
     */
    public function import_invoices($source, array $id_map) {
        $this->invoice_import_source = $source;
        $this->invoice_import_order_ids = $id_map;

        $invoice_map = array(
            // see also: event_before_insert_payment_invoice()
            'Payment_System_ID' => 'Classificator_PaymentSystem.PaymentSystem_ID',
            'Customer_ID' => 'User_ID',
        );
        $this->dumper->import_data('Payment_Invoice', null, $invoice_map);

        $this->invoice_import_source = null;
        $this->invoice_import_order_ids = null;
    }

    /**
     * @param array $row
     * @return false|array
     */
    public function event_before_insert_payment_invoice($row) {
        if ($row['Order_Source'] != $this->invoice_import_source) { return false; }
        $row['Order_ID'] = $this->invoice_import_order_ids[$row['Order_ID']];
        return $row;
    }
}