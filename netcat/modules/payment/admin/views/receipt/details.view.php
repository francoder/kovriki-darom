<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_receipt $receipt */
/** @var array $events */

$invoice = $receipt->get_invoice() ?: new nc_payment_invoice();

?>

<!-- обёртка для того, чтобы блоки и таблицы были одинаковой ширины (но не больше необходимой) -->
<div style="display: inline-block">
    <? if ($invoice->get_id()): ?>
        <div class="nc-margin-bottom-small">
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CUSTOMER ?>:
                <?= nc_payment_invoice_admin_helpers::get_customer_contacts_string($invoice) ?>
            </div>
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE ?>:
                <?= nc_payment_invoice_admin_helpers::get_invoice_link($invoice) ?>
            </div>
        </div>
    <? endif; ?>
    <div class="nc-margin-bottom-medium">
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_CREATED ?>:
            <?= nc_payment_admin_helpers::format_time($receipt->get('created')) ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION ?>:
            <?= nc_payment_receipt_admin_helpers::get_operation_string($receipt) ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_AMOUNT ?>:
            <?= nc_payment_admin_helpers::format_number($receipt->get('amount')) ?> <?= $invoice->get_currency() ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTER ?>:
            <?= nc_payment_receipt_admin_helpers::get_register_provider_string($receipt) ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_STATUS ?>:
            <?php
                list($status) = nc_payment_receipt_admin_helpers::get_status_description($receipt);
                echo $status;
            ?>
        </div>
        <? if ($receipt->get('fiscal_receipt_created')): ?>
            <div class="nc-margin-top-small">
                <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTRATION_TIME ?>:
                <?= nc_payment_admin_helpers::format_time($receipt->get('fiscal_receipt_created')) ?>
            </div>
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SHIFT_NUMBER ?>:
                <?= $receipt->get('shift_number') ?>
            </div>
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SERIAL_NUMBER ?>:
                <?= $receipt->get('fiscal_receipt_number') ?>
            </div>
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_FISCAL_STORAGE_NUMBER ?>:
                <?= $receipt->get('fiscal_storage_number') ?>
            </div>
            <? if ($receipt->get('register_registration_number')): ?>
                <div>
                    <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTER_NUMBER ?>:
                    <?= $receipt->get('register_registration_number') ?>
                </div>
            <? endif; ?>
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_FISCAL_DOCUMENT_NUMBER ?>:
                <?= $receipt->get('fiscal_document_number') ?>
            </div>
            <div>
                <?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_FISCAL_DOCUMENT_ATTRIBUTE ?>:
                <?= $receipt->get('fiscal_document_attribute') ?>
            </div>
        <? endif; ?>
    </div>

    <h3><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_ITEMS ?></h3>
    <?= $this->include_view('../includes/item_list')->with('items', $receipt->get_items()) ?>
</div>

<? if ($events): ?>
    <div></div>
    <div style="display: inline-block">
        <h3><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_EVENTS ?></h3>
        <?= $this->include_view('../includes/event_list')->with('events', $events) ?>
    </div>
<? endif; ?>