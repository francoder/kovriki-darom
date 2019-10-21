<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_invoice[] $invoices */
/** @var int $page */
/** @var int $total_pages */
/** @var string $page_url */
/** @var string $search */

?>
<form method="get" class="nc-payment-admin-invoice-search-form">
    <input type="hidden" name="controller" value="invoice">
    <input type="hidden" name="action" value="index">

    <?= $ui->controls->site_select($site_id); ?>
    <input type="search" name="search" value="<?= htmlspecialchars($search) ?>"
            placeholder="<?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SEARCH_PLACEHOLDER ?>" size="50">
    <input type="submit" class="nc-btn nc--blue" value="<?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SEARCH_BUTTON ?>">
</form>

<?php

if (!count($invoices)) {
    echo $ui->alert->info(NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_NO_MATCH);
    return;
}

?>

<table class="nc-table nc--bordered nc--wide nc-margin-bottom-medium">
    <tr>
        <th class="nc--compact"></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_CREATED ?></th>
        <th class="nc-text-right"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_AMOUNT ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SOURCE ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CUSTOMER ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_PAYMENT_SYSTEM ?></th>
    </tr>
    <? foreach ($invoices as $invoice): ?>
        <?php
            $view_link = "#module.payment.invoice.view(" . $invoice->get_id() . ")";
        ?>
        <tr>
            <td>
                <?php
                    list(, $status_caption, $status_color) = nc_payment_invoice_admin_helpers::get_status_description($invoice);
                    $status_label = $ui->label($status_caption)->wide()->$status_color();
                    echo $ui->helper->hash_link($view_link, $status_label);
                ?>
            </td>
            <td><strong><?= $ui->helper->hash_link($view_link, $invoice->get_id()) ?></strong></td>
            <td><?= nc_payment_admin_helpers::format_time($invoice->get('created')) ?></td>
            <td class="nc-text-right"><?= nc_payment_admin_helpers::format_number($invoice->get('amount')) ?></td>
            <td><?= nc_payment_invoice_admin_helpers::get_source_string($invoice, $site_id) ?></td>
            <td><?= nc_payment_invoice_admin_helpers::get_customer_contacts_string($invoice) ?></td>
            <td><?= nc_get_list_item_name('PaymentSystem', $invoice->get('payment_system_id')) ?></td>
        </tr>
    <? endforeach; ?>
</table>

<?= $this->include_view('../includes/pagination', $this->data) ?>
