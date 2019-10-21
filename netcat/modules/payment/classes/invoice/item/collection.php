<?php

class nc_payment_invoice_item_collection extends nc_record_collection {

    protected $items_class = 'nc_payment_invoice_item';
    protected $index_property = 'id'; // при изменении необходимо скорректировать nc_payment_invoice

}