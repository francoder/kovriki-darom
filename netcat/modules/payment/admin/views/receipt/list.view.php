<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_receipt[] $receipts */
/** @var int $page */
/** @var int $total_pages */
/** @var string $page_url */

echo $ui->controls->site_select($site_id);
?>

<table class="nc-table nc--bordered nc--wide nc-margin-bottom-medium">
    <tr>
        <th class="nc--compact"></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_AMOUNT ?>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_CREATED ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTRATION_TIME ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SHIFT_NUMBER ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SERIAL_NUMBER ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SOURCE ?></th>
    </tr>
    <? foreach ($receipts as $receipt): ?>
        <?php
            $view_link = "#module.payment.receipt.view(" . $receipt->get_id() . ")";
            $invoice = $receipt->get_invoice();
        ?>
        <tr>
            <td>
                <?php
                    list($status_caption, $status_color) = nc_payment_receipt_admin_helpers::get_status_description($receipt);
                    $status_label = $ui->label($status_caption)->wide()->$status_color();
                    echo $ui->helper->hash_link($view_link, $status_label);
                ?>
            </td>
            <td><strong><?= $ui->helper->hash_link($view_link, $receipt->get_id()) ?></strong></td>
            <td><?= nc_payment_receipt_admin_helpers::get_operation_string($receipt) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($receipt->get('amount')) ?></td>
            <td><?= nc_payment_admin_helpers::format_time($receipt->get('created')) ?></td>
            <td><?= nc_payment_admin_helpers::format_time($receipt->get('fiscal_receipt_created')) ?></td>
            <td><?= $receipt->get('shift_number') ?></td>
            <td><?= $receipt->get('fiscal_receipt_number') ?></td>
            <td><?= $invoice ? nc_payment_invoice_admin_helpers::get_invoice_link($invoice) : '' ?></td>
            <td><?= $invoice ? nc_payment_invoice_admin_helpers::get_source_string($invoice) : '' ?></td>
        </tr>
    <? endforeach; ?>
</table>

<?= $this->include_view('../includes/pagination', $this->data) ?>
