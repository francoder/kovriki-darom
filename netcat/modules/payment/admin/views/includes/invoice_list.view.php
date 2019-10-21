<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_invoice_collection $invoices */
/** @var int $current_invoice_id */

$total_amount = 0;
$total_paid_amount = 0;
$total_remaining_amount = 0;

?>

<table class="nc-table nc--wide nc--hovered nc-margin-bottom-medium">
    <tr class="nc-bg-lighten">
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_STATUS ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_CREATED ?></th>
        <th class="nc-text-right" width="150"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_AMOUNT_SHORT ?></th>
        <th class="nc-text-right" width="150"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ALREADY_PAID ?></th>
        <th class="nc-text-right" width="150"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_BALANCE ?></th>
    </tr>
    <? foreach ($invoices as $invoice): ?>
        <?php
            $is_cancelled = $invoice->get('status') == nc_payment_invoice::STATUS_CANCELLED;

            $amount = $invoice->get('amount');
            $paid_amount = $invoice->get('status') == nc_payment_invoice::STATUS_SUCCESS ? $amount : 0;
            $remaining_amount = $amount - $paid_amount;

            if (!$is_cancelled) {
                $total_amount += $amount;
                $total_paid_amount += $paid_amount;
                $total_remaining_amount += $remaining_amount;
            }
        ?>
        <tr class="<?= $is_cancelled ? 'nc--disabled' : '' ?>">
            <td>
                <strong>
                    <? if ($invoice->get_id() == $current_invoice_id): ?>
                        <?= $current_invoice_id ?>
                    <? else: ?>
                        <?= nc_payment_invoice_admin_helpers::get_invoice_link($invoice) ?>
                    <? endif; ?>
                </strong>
            </td>
            <td>
                <?php
                    list(, $status_caption) = nc_payment_invoice_admin_helpers::get_status_description($invoice);
                    echo $status_caption;
                ?>
            </td>
            <td><?= nc_payment_admin_helpers::format_time($invoice->get('created')) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($amount) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($paid_amount) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($remaining_amount) ?></td>
        </tr>
    <? endforeach; ?>
    <tr>
        <th colspan="3"><?= NETCAT_MODULE_PAYMENT_ADMIN_TOTALS ?></th>
        <th class="nc-text-right"><?= nc_payment_admin_helpers::format_number($total_amount) ?></th>
        <th class="nc-text-right"><?= nc_payment_admin_helpers::format_number($total_paid_amount) ?></th>
        <th class="nc-text-right"><?= nc_payment_admin_helpers::format_number($total_remaining_amount) ?></th>
    </tr>
</table>
