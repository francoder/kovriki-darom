<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var nc_payment_invoice $invoice */
/** @var nc_record_collection $all_order_invoices */
/** @var array $site_payment_systems */
/** @var bool $show_payment_link */
/** @var string $payment_script_url */
/** @var bool $are_receipts_enabled */

$receipts = $invoice->get_all_receipts(false);
$items = $invoice->get_items();

?>

<!-- обёртка для того, чтобы таблицы были одинаковой ширины (но не больше необходимой) -->
<div style="display: inline-block">
    <h3>
        <strong><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE ?> <?= $invoice->get_id() ?></strong><br>
        <?= $invoice->get_description() ?>
    </h3>
    <?php
        if ($invoice->get('amount') < 0) {
            echo $ui->alert->info(NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_MANUAL_REFUND);
        }
    ?>
    <div class="nc-margin-top-small">
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_AMOUNT ?>:
            <?= nc_payment_admin_helpers::format_number($invoice->get_amount()) ?> <?= $invoice->get('currency') ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CUSTOMER ?>:
            <?= nc_payment_invoice_admin_helpers::get_customer_contacts_string($invoice) ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SOURCE ?>:
            <?= nc_payment_invoice_admin_helpers::get_source_string($invoice, $site_id) ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_CREATED ?>:
            <?= nc_payment_admin_helpers::format_time($invoice->get('created')) ?>
        </div>
        <div>
            <?= NETCAT_MODULE_PAYMENT_ADMIN_STATUS ?>:
            <?php
                list($status_caption) = nc_payment_invoice_admin_helpers::get_status_description($invoice);
                echo $status_caption;
            ?>
            &centerdot;
            <a href="#" class="nc-payment-admin-invoice-change-status"><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CHANGE ?></a>
            <div style="display: none"
            class="nc-padding-10 nc-margin-vertical-small nc-bg-lighten nc-payment-admin-invoice-change-status-container">
                <form method="post">
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="id" value="<?= $invoice->get_id() ?>">
                    <?php
                        if ($are_receipts_enabled && !$receipts->count() && $invoice->get('status') != nc_payment_invoice::STATUS_SUCCESS) {
                            $sell_totals = $items->where('operation', nc_payment::OPERATION_SELL)->sum('total_price');
                            $refund_totals = $items->where('operation', nc_payment::OPERATION_SELL_REFUND)->sum('total_price');

                            if ($sell_totals && $refund_totals) {
                                $message = sprintf(
                                        NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ON_STATUS_CHANGE_SELL_SELL_REFUND_RECEIPTS,
                                        nc_payment_admin_helpers::format_number($sell_totals),
                                        nc_payment_admin_helpers::format_number($refund_totals)
                                );
                            } else if ($sell_totals) {
                                $message = sprintf(
                                        NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ON_STATUS_CHANGE_SELL_RECEIPT,
                                        nc_payment_admin_helpers::format_number($sell_totals)
                                );
                            } else if ($refund_totals) {
                                // предупредить о невозможности создания чека, если чеки выдаются через платёжную систему
                                // и она не поддерживает частичные возвраты
                                if (!nc_payment_register::is_enabled($invoice->get('site_id'))) {
                                    $message = NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_REFUND_PAYMENT_SYSTEM_NO_RECEIPT_WARNING;
                                } else {
                                    $message = sprintf(
                                            NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ON_STATUS_CHANGE_SELL_REFUND_RECEIPT,
                                            nc_payment_admin_helpers::format_number($refund_totals)
                                    );
                                }

                            } else {
                                $message = null;
                            }

                            if ($message) {
                                echo $ui->alert->info($message)->style('margin-top: 0');
                            }
                        }
                    ?>
                    <select name="new_status">
                        <?php
                        $statuses = array(
                            nc_payment_invoice::STATUS_NEW => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_NEW_SHORT,
                            // nc_payment_invoice::STATUS_WAITING => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_WAITING,
                            nc_payment_invoice::STATUS_SUCCESS => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SUCCESS,
                            // nc_payment_invoice::STATUS_REJECTED => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_REJECTED,
                            nc_payment_invoice::STATUS_CANCELLED => NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CANCELLED,
                        );
                        foreach ($statuses as $status_id => $description) {
                            echo '<option value="' . htmlspecialchars($status_id) . '" ' .
                                ($status_id == $invoice->get('status') ? ' selected' : '') . '>' .
                                htmlspecialchars($description) .
                                '</option>';
                        }
                        ?>
                    </select>
                    <input class="nc-btn" type="submit" value="<?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CHANGE ?>">
                </form>
                <script>
                    $nc(function() {
                        $nc('.nc-payment-admin-invoice-change-status').click(function() {
                            $nc('.nc-payment-admin-invoice-change-status-container').toggle();
                            return false;
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>
<div></div>
<!-- обёртка для того, чтобы таблицы были одинаковой ширины (но не больше необходимой) -->
<div style="display: inline-block">
    <div class="nc-margin-bottom-medium">
        <div>
            <?= NETCAT_MODULE_PAYMENT_PAYMENT_SYSTEM ?>:
            <?= nc_get_list_item_name('PaymentSystem', $invoice->get('payment_system_id')) ?>
            <? if ($invoice->get('last_response')): ?>
                &centerdot;
                <a href="#" class="nc-payment-admin-invoice-show-last-response">
                    <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_LAST_RESPONSE ?>
                </a>
                <pre style="display: none" class="nc-padding-10 nc-bg-lighten"><?php
                    $last_response = $invoice->get('last_response');
                    $data = json_decode($last_response, true);
                    if ($data) {
                        echo var_export($data, true);
                    } else {
                        echo $last_response;
                    }
                ?></pre>
                <script>
                    $nc(function () {
                        $nc('.nc-payment-admin-invoice-show-last-response').click(function () {
                            $nc(this).closest('div').find('pre').toggle();
                            return false;
                        });
                    });
                </script>
            <? endif; ?>
        </div>
        <? if ($show_payment_link): ?>
            <div>
                <a href="#" class="nc-payment-admin-invoice-show-payment-link">
                    <?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_PAYMENT_LINK ?>
                </a>
                <div style="display: none"
                    class="nc-padding-10 nc-margin-vertical-small nc-bg-lighten nc-payment-admin-invoice-show-payment-container">
                    <select>
                        <?php
                        foreach ($site_payment_systems as $k => $v) {
                            echo '<option value="' . htmlspecialchars($k) . '" ' .
                                ($k == $invoice->get('payment_system_id') ? ' selected' : '') . '>' .
                                htmlspecialchars($v) .
                                '</option>';
                        }
                        ?>
                    </select>
                    <input type="text">
                    <a href="#"><?= NETCAT_MODULE_PAYMENT_ADMIN_COPY_TO_CLIPBOARD ?></a>
                </div>
            </div>
            <script>
                $nc(function () {
                    var link_container = $nc('.nc-payment-admin-invoice-show-payment-container'),
                        select = link_container.find('select'),
                        input = link_container.find('input'),
                        copy = link_container.find('a');

                    // обновление ссылки для оплаты
                    function make_link() {
                        input.val(
                            '<?= $payment_script_url ?>?payment_system=' + select.val() +
                            '&invoice_id=<?= $invoice->get_id() ?>'
                        );
                    }
                    select.change(make_link);
                    make_link();

                    // копирование ссылки в буфер
                    input.on('focus', function() {
                        input.select();
                    });

                    try {
                        if (!document.queryCommandSupported('copy')) {
                            copy.hide();
                        }
                    } catch (e) {
                        copy.hide();
                    }

                    copy.click(function() {
                        try {
                            input.select();
                            document.execCommand('copy');
                        } catch (e) {
                            input.hide()
                        }
                        return false;
                    });

                    $nc('.nc-payment-admin-invoice-show-payment-link').click(function() {
                        link_container.toggle();
                        return false;
                    });
                });
            </script>
        <? endif; ?>
    </div>

    <h3><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEMS ?></h3>
    <?= $this->include_view('../includes/item_list')->with('items', $items) ?>

    <? if ($receipts->count()): ?>
        <h3><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPTS ?></h3>
        <?= $this->include_view('../includes/receipt_list')->with('receipts', $receipts) ?>
    <? endif; ?>

    <? if ($all_order_invoices->count() > 1): ?>
        <h3><?= NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ALL_FOR_ORDER ?></h3>
        <?php
            echo $this->include_view('../includes/invoice_list')
                      ->with('invoices', $all_order_invoices)
                      ->with('current_invoice_id', $id);

            /** @var nc_payment_invoice_collection $non_cancelled_invoices */
            $non_cancelled_invoices = $all_order_invoices->where('status', nc_payment_invoice::STATUS_CANCELLED, '!=');

            if ($non_cancelled_invoices->count() > 1) {
                echo '<h3>',
                     NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_BALANCE, ' ',
                     nc_payment_invoice_admin_helpers::get_invoice_links($non_cancelled_invoices, $id),
                     '</h3>';

                echo $this->include_view('../includes/item_list')
                          ->with('items', $non_cancelled_invoices->get_items_balance());
            }
        ?>
    <? endif; ?>
</div>