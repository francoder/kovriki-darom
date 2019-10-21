<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_receipt_collection $receipts */

?>

<table class="nc-table nc--wide nc--hovered nc-margin-bottom-medium">
    <tr class="nc-bg-lighten">
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_ID ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_STATUS ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SHIFT_NUMBER ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SERIAL_NUMBER ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTRATION_TIME ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_AMOUNT ?></th>
    </tr>

    <? foreach ($receipts as $receipt): ?>
        <?php
            $receipt_id = $receipt->get_id();
            $details_link = "#module.payment.receipt.view($receipt_id)";
            ini_set('display_errors',1);
        ?>
        <tr>
            <td><strong><?= $ui->helper->hash_link($details_link, $receipt_id) ?></strong></td>
            <td><?= nc_payment_receipt_admin_helpers::get_operation_string($receipt) ?></td>
            <td>
                <?php
                    list($status_caption) = nc_payment_receipt_admin_helpers::get_status_description($receipt);
                    echo $status_caption;
                ?>
            </td>
            <td class="nc-text-center"><?= $receipt->get('shift_number') ?></td>
            <td class="nc-text-center"><?= $receipt->get('fiscal_receipt_number') ?>
            <td class="nc-text-center"><?= nc_payment_admin_helpers::format_time($receipt->get('fiscal_receipt_created')) ?>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($receipt->get('amount')) ?>
        </tr>
    <? endforeach; ?>
    <? if ($receipts->count() > 1): ?>
        <tr>
            <th colspan="6" class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_BALANCE ?></th>
            <th class="nc-text-right"><?= nc_payment_admin_helpers::format_number($receipts->sum('registered_signed_amount')) ?>
        </tr>
    <? endif; ?>
</table>
