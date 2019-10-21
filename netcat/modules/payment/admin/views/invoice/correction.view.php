<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_invoice $invoice */
/** @var nc_payment_invoice_collection $all_order_invoices */

$items = $all_order_invoices->get_items_balance();
$totals = $items->sum('total_price');

$paid_invoices = $all_order_invoices->where('status', nc_payment_invoice::STATUS_SUCCESS);
$unpaid_invoices = $all_order_invoices->where('status', nc_payment_invoice::STATUS_SUCCESS, '!=');
$paid_amount = 0;

?>
<form class="nc-form" method="post" style="display: inline-block">
    <input type="hidden" name="action" value="create_correction_invoice">
    <input type="hidden" name="primary_invoice_id" value="<?= $all_order_invoices->first()->get_id() ?>">

    <? if (!empty($error)): ?>
        <?= $ui->alert->error($error) ?>
    <? endif; ?>

    <div class="nc--clearfix nc-margin-bottom-small">
        <h3>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_HEADER ?>
            <?= nc_payment_invoice_admin_helpers::get_source_string($all_order_invoices->first()) ?>
            <a class="nc-btn nc--red nc--small nc-payment-admin-invoice-correction-refund-all" style="float: right">
                <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_FULL_REFUND_BUTTON ?>
            </a>
        </h3>
        <? if ($all_order_invoices->count() > 1): ?>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_BALANCE ?>
        <? else: ?>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_PRIMARY_INVOICE ?>
        <? endif; ?>
        <?= nc_payment_invoice_admin_helpers::get_invoice_links($all_order_invoices) ?>
    </div>

    <?php
        if ($unpaid_invoices->count()) {
            echo $ui->alert->info(
                NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_CANCELLED_INVOICES . ' ' .
                nc_payment_invoice_admin_helpers::get_invoice_links($unpaid_invoices)
            );
        }
    ?>

    <table class="nc-table nc-payment-admin-invoice-correction-table nc--wide nc--hovered nc-margin-bottom-large">
        <thead>
            <tr class="nc-bg-lighten">
                <th rowspan="2"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_NAME ?></th>
                <th rowspan="2" class="nc-text-center" width="20"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_RATE ?></th>
                <th rowspan="2" class="nc-text-center" width="80"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_PRICE ?></th>
                <th colspan="2" class="nc-payment-admin-invoice-paid"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ALREADY_PAID ?></th>
                <th colspan="3" class="nc-payment-admin-invoice-after-change"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_AFTER ?></th>
                <th rowspan="2" class="nc-text-center" width="80"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_BALANCE ?></th>
            </tr>
            <tr class="nc-bg-lighten">
                <th class="nc-payment-admin-invoice-paid" width="80"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_QTY ?></th>
                <th class="nc-payment-admin-invoice-paid" width="80"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUM ?></th>
                <th class="nc-payment-admin-invoice-after-change" width="1"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_FULL_REFUND_CHECKBOX ?></th>
                <th class="nc-payment-admin-invoice-after-change" width="80"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_QTY ?></th>
                <th class="nc-payment-admin-invoice-after-change" width="80"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUM ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($items as $i => $item): ?>
            <tr data-item-price="<?= sprintf('%.2F', $item['item_price']) ?>"
                data-qty="<?= sprintf('%.3F', $item['qty']) ?>"
                data-paid-qty="<?= sprintf('%.3F', $item['paid_qty']) ?>"
                class="nc-payment-admin-invoice-correction-table-item">

                <td>
                    <?= nc_payment_invoice_admin_helpers::get_item_link($item) ?>
                    <?php
                        $paid_amount += $item['signed_paid_amount'];
                        foreach ($item as $name => $value) {
                            echo $ui->html->input('hidden', "existing[$i][$name]", $value);
                        }
                    ?>
                </td>
                <td class="nc-text-center"><?= nc_payment_admin_helpers::format_tax_rate($item['vat_rate']) ?></td>
                <td class="nc-text-right">
                    <?= nc_payment_admin_helpers::format_number($item['item_price']) ?>
                </td>
                <td class="nc-payment-admin-invoice-paid nc-text-right">
                    <?= $item['paid_qty'] ?>
                </td>
                <td class="nc-payment-admin-invoice-paid nc-text-right">
                    <?= nc_payment_admin_helpers::format_number($item['signed_paid_amount']) ?>
                </td>
                <td class="nc-payment-admin-invoice-after-change nc-text-center">
                    <label><input type="checkbox" class="nc-payment-admin-invoice-item-full-refund"></label>
                </td>
                <td class="nc-payment-admin-invoice-after-change">
                    <input type="number" min="0" step="any"
                        name="<?= "existing[$i][new_qty]" ?>"
                        value="<?= $item['qty'] ?>"
                        class="nc-input nc--wide nc-payment-admin-invoice-item-qty">
                </td>
                <td class="nc-payment-admin-invoice-after-change nc-text-right nc-payment-admin-invoice-item-total-price">
                    <?= nc_payment_admin_helpers::format_number($item['total_price']) ?>
                </td>
                <td class="nc-text-right nc-payment-admin-invoice-item-balance">
                    <?= nc_payment_admin_helpers::format_number($item['total_price'] - $item['signed_paid_amount']) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="nc-payment-admin-invoice-correction-table-summary">
                <th colspan="4"><?= NETCAT_MODULE_PAYMENT_ADMIN_TOTALS ?></th>
                <th class="nc-text-right">
                    <?= nc_payment_admin_helpers::format_number($paid_amount) ?>
                </th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th class="nc-text-right nc-payment-admin-invoice-summary-total-price">
                    <?= nc_payment_admin_helpers::format_number($totals) ?>
                </th>
                <th class="nc-text-right nc-payment-admin-invoice-summary-balance">
                    <?= nc_payment_admin_helpers::format_number($totals - $paid_amount) ?>
                </th>
            </tr>
        </tfoot>
    </table>

    <h3><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_ADD_ITEMS ?></h3>

    <table class="nc-table nc-payment-admin-invoice-new-items-table nc--wide nc--hovered nc-margin-top-small nc-margin-bottom-large">
        <thead>
            <tr class="nc-bg-lighten">
                <th><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_NAME ?></th>
                <th width="80" class="nc--nowrap nc-text-center"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_RATE ?></th>
                <th width="120" class="nc-text-center"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_PRICE ?></th>
                <th width="100" class="nc-text-center"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_QTY ?></th>
                <th width="80" class="nc-text-center"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_TOTALS ?></th>
                <th width="40">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr style="display: none" class="nc-payment-admin-invoice-new-items-table-summary">
                <th colspan="4"><?= NETCAT_MODULE_PAYMENT_ADMIN_TOTALS ?></th>
                <th class="nc-text-center nc-payment-admin-invoice-new-items-table-summary-amount">
                    <?= nc_payment_admin_helpers::format_number(0) ?>
                </th>
                <th>&nbsp;</th>
            </tr>
            <tr class="nc-payment-admin-invoice-new-items-row nc-payment-admin-invoice-new-items-row-template">
                <td>
                    <input type="text" name="@[name]" class="nc--wide">
                    <input type="hidden" name="@[source_component_id]">
                    <input type="hidden" name="@[source_item_id]">
                </td>
                <td>
                    <select name="@[vat_rate]" class="nc--wide">
                        <option value=""><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_NONE ?></option>
                        <option value="0">0%</option>
                        <option value="10">10%</option>
                        <option value="18" selected>18%</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="@[item_price]" min="0" step="any" class="nc--wide nc-payment-admin-invoice-new-item-price">
                </td>
                <td>
                    <input type="number" name="@[qty]" min="0" step="any" class="nc--wide nc-payment-admin-invoice-new-item-qty" value="1">
                </td>
                <td class="nc-text-center nc-payment-admin-invoice-new-item-total-price">
                    <?= nc_payment_admin_helpers::format_number(0) ?>
                </td>
                <td class="nc-text-center">
                    <a href="#" class="nc-payment-admin-invoice-new-items-add-link">
                        <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_ADD_ITEM ?>
                    </a>
                    <a href="#" class="nc-payment-admin-invoice-new-items-remove-link">
                        <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_REMOVE_ITEM ?>
                    </a>
                </td>
            </tr>
        </tfoot>
    </table>

    <h3>
        <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUMMARY_AMOUNT ?><span
            class="nc-payment-admin-invoice-correction-type"><span
                class="nc-payment-admin-invoice-correction-type-none">:</span>
            <span style="display: none" class="nc-payment-admin-invoice-correction-type-extra">
                (<?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUMMARY_EXTRA  ?>):
            </span>
            <span style="display: none" class="nc-payment-admin-invoice-correction-type-refund">
                (<?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUMMARY_REFUND  ?>):
            </span>
        </span>
        <strong class="nc-payment-admin-invoice-correction-total-amount">
            <?= nc_payment_admin_helpers::format_number(0) ?>
        </strong>
    </h3>
    <div class="nc-margin-top-medium"></div>
</form>

<script>
$nc(function() {
    function format(number) {
        var formatted_number = Number(number).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
            classes = 'nc--nowrap';
        if (number < 0) {
            if (Math.abs(number) >= 0.01) {
                formatted_number = formatted_number.replace('-', '&minus;');
                classes += ' nc-text-red';
            } else {
                formatted_number = formatted_number.replace('-', '');
            }
        }
        return '<span class="' + classes + '">' + formatted_number + '</span>';
    }

    var existing_change_amount = 0,
        new_amount = 0;

    function recalculate_totals() {
        var totals = existing_change_amount + new_amount;
        $nc('.nc-payment-admin-invoice-correction-total-amount').html(format(totals));
        $nc('.nc-payment-admin-invoice-correction-type > span').hide();
        if (totals < 0) {
            $nc('.nc-payment-admin-invoice-correction-type-refund').show();
        } else if (totals > 0) {
            $nc('.nc-payment-admin-invoice-correction-type-extra').show();
        } else {
            $nc('.nc-payment-admin-invoice-correction-type-none').show();
        }
    }

    // --- Работа с таблицей существующих позиций ---
    var correction_table = $nc('.nc-payment-admin-invoice-correction-table'),
        corrected_items = correction_table.find('tbody tr');

    function recalculate_existing() {
        var paid_amount = <?= sprintf('%.2F', $paid_amount) ?>,
            changed_totals = 0;
        corrected_items.each(function() {
            var item = $nc(this),
                item_price = item.data('itemPrice'),
                paid_qty = item.data('paidQty'),
                new_qty = Math.max(0, item.find('.nc-payment-admin-invoice-item-qty').val()),
                new_total_cost = item_price * new_qty,
                item_balance = item_price * (new_qty - paid_qty);
            item.find('.nc-payment-admin-invoice-item-full-refund').prop('checked', new_qty == 0);
            item.find('.nc-payment-admin-invoice-item-total-price').html(format(new_total_cost));
            item.find('.nc-payment-admin-invoice-item-balance').html(format(item_balance));
            changed_totals += new_total_cost;
        });
        correction_table.find('.nc-payment-admin-invoice-summary-total-price').html(format(changed_totals));
        existing_change_amount = changed_totals - paid_amount;
        correction_table.find('.nc-payment-admin-invoice-summary-balance').html(format(existing_change_amount));
        recalculate_totals();
    }

    correction_table.find('input[type=number]').change(recalculate_existing);

    $nc('.nc-payment-admin-invoice-correction-refund-all').click(function() {
        var checkboxes = correction_table.find('.nc-payment-admin-invoice-item-full-refund'),
            inputs = correction_table.find('.nc-payment-admin-invoice-item-qty');
        if (checkboxes.length === checkboxes.filter(':checked').length) {
            // отмена полного возврата
            checkboxes.prop('checked', false);
            inputs.each(function() {
                var input = $nc(this);
                input.val(input.closest('tr').data('qty'));
            });
        } else {
            checkboxes.prop('checked', true);
            inputs.val(0);
        }
        recalculate_existing();
    });

    $nc('.nc-payment-admin-invoice-item-full-refund').change(function() {
        var checkbox = $nc(this),
            item = checkbox.closest('tr'),
            qty = item.find('.nc-payment-admin-invoice-item-qty');
        qty.val(checkbox.prop('checked') ? 0 : item.data('qty'));
        recalculate_existing();
    });

    recalculate_existing();

    // --- работа с таблицей новых позиций ---
    var add_items_row = $nc('.nc-payment-admin-invoice-new-items-row-template'),
        new_items_table = $nc('.nc-payment-admin-invoice-new-items-table'),
        new_items_tbody = new_items_table.find('tbody'),
        next_items_row_number = 0;

    function toggle_new_items_summary_row() {
        $nc('.nc-payment-admin-invoice-new-items-table-summary').toggle(new_items_tbody.children().length > 0);
    }

    function recalculate_new() {
        var rows = new_items_table.find('.nc-payment-admin-invoice-new-items-row');
        new_amount = 0;
        rows.each(function() {
            var row = $nc(this),
                row_totals =
                    row.find('.nc-payment-admin-invoice-new-item-price').val() *
                    row.find('.nc-payment-admin-invoice-new-item-qty').val();
            row.find('.nc-payment-admin-invoice-new-item-total-price').html(format(row_totals));
            if (!row.is('.nc-payment-admin-invoice-new-items-row-template')) {
                new_amount += row_totals;
            }
        });
        new_items_table.find('.nc-payment-admin-invoice-new-items-table-summary-amount').html(format(new_amount));
        recalculate_totals();
    }

    add_items_row.find('input[type=number]').on('input', recalculate_new);

    add_items_row.find('.nc-payment-admin-invoice-new-items-add-link').click(function() {
        var name_input = add_items_row.find('input[name="@[name]"]'),
            price_input = add_items_row.find('input[name="@[item_price]"]'),
            qty_input = add_items_row.find('input[name="@[qty]"]'),
            vat_select = add_items_row.find('select[name="@[vat_rate]"]');

        if (!name_input.val().length) {
            name_input.focus();
            return false;
        }

        if (!price_input.val().length) {
            price_input.focus();
            return false;
        }

        if (qty_input.val() <= 0) {
            qty_input.focus();
            return false;
        }

        var inputs_name_prefix = 'new[' + (next_items_row_number++) + ']',
            new_row = add_items_row.clone().appendTo(new_items_tbody);

        new_row.removeClass('nc-payment-admin-invoice-new-items-row-template');
        new_row.find('select[name="@[vat_rate]"]').val(vat_select.val()); // wtf
        new_row.find('input[type=number]').on('input', recalculate_new);
        new_row.find('input, select').each(function() {
            this.name = this.name.replace('@', inputs_name_prefix);
        });
        new_row.find('.nc-payment-admin-invoice-new-items-remove-link').click(function() {
            $nc(this).closest('tr').remove();
            toggle_new_items_summary_row();
            return false;
        });

        add_items_row.find('input').val('');
        qty_input.val(1);
        add_items_row.find('.nc-payment-admin-invoice-new-item-total-price').html(format(0));
        name_input.focus();

        recalculate_new();
        toggle_new_items_summary_row();

        return false;
    });

});
</script>

