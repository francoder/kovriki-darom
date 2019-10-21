<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_invoice_item_collection $items */

?>

<table class="nc-table nc--wide nc--hovered nc-margin-bottom-medium">
    <tr class="nc-bg-lighten">
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_NAME ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_PRICE ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_QTY ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_RATE ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_AMOUNT ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_TOTALS ?></th>
    </tr>

    <? /** @var nc_payment_invoice_item $item */ ?>
    <? foreach ($items as $item): ?>
        <tr>
            <td><?= nc_payment_invoice_admin_helpers::get_item_link($item) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($item->get('signed_item_price')) ?>
            <td class="nc-text-right"><?= $item->get('qty') ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_tax_rate($item->get('vat_rate')) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($item->get('signed_total_vat_amount')) ?>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($item->get('signed_total_price')) ?>
        </tr>
    <? endforeach; ?>
    <? if ($items->count() > 1): ?>
        <tr>
            <th colspan="3"><?= NETCAT_MODULE_PAYMENT_ADMIN_TOTALS ?></th>
            <th>&nbsp;</th>
            <th class="nc-text-right"><?= nc_payment_admin_helpers::format_number($items->sum('signed_total_vat_amount')) ?>
            <th class="nc-text-right"><?= nc_payment_admin_helpers::format_number($items->sum('signed_total_price')) ?>
        </tr>
    <? endif; ?>
</table>
