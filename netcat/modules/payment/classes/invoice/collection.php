<?php

class nc_payment_invoice_collection extends nc_record_collection {

    protected $items_class = 'nc_payment_invoice';

    /**
     * Возвращает коллекцию позиций счетов с итоговым количеством.
     * Позиции считаются одинаковыми при совпадении значений свойств
     * 'name', 'item_price', 'vat_rate', 'source_item_id', 'source_component_id'.
     *
     * @return nc_payment_invoice_item_collection
     */
    public function get_items_balance() {
        $result = new nc_payment_invoice_item_collection();

        /** @var nc_payment_invoice $invoice */
        foreach ($this->items as $invoice) {
            foreach ($invoice->get_items() as $invoice_item) {
                $existing_result_item = $result->first('is_same', true, '==', array($invoice_item));
                if ($existing_result_item) {
                    $existing_result_item['qty'] += $invoice_item['signed_qty'];
                    $existing_result_item['paid_qty'] += $invoice_item['signed_paid_qty'];
                } else {
                    $new_result_item = clone $invoice_item;
                    $new_result_item['qty'] = $invoice_item['signed_qty'];
                    $new_result_item['paid_qty'] = $invoice_item['signed_paid_qty'];
                    $new_result_item['operation'] = nc_payment::OPERATION_SELL;
                    $result->add($new_result_item);
                }
            }
        }

        return $result;
    }

}