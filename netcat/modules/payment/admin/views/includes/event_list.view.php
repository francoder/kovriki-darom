<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var array $events */
/** @var bool $show_receipt_link */

if (empty($events)) {
    return;
}

$nc_core = nc_core::get_object();
$show_receipt_link = !empty($show_receipt_link);

?>

<table class="nc-table nc--wide nc-margin-bottom-medium">
    <tr class="nc-bg-lighten">
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_LOG_TIME ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_LOG_EVENT ?></th>
        <? if ($show_receipt_link): ?>
            <th><?= NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT ?></th>
        <? endif; ?>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_LOG_RECEIPT_STATUS ?></th>
        <th>&nbsp;</th>
    </tr>
    <? foreach ($events as $event): ?>
        <?php
            $text_class = $event['EventType'] == nc_payment_register::LOG_TYPE_ERROR ? ' class="nc-text-red"' : '';
        ?>
        <tr>
            <td<?= $text_class ?>><?= nc_payment_admin_helpers::format_time($event['Created']) ?></td>
            <td<?= $text_class ?>><?= $event['Message'] ?></td>
            <? if ($show_receipt_link): ?>
                <td><?= nc_payment_receipt_admin_helpers::get_receipt_link_by_id($event['Payment_Receipt_ID']) ?></td>
            <? endif; ?>
            <td<?= $text_class ?>>
                <?php
                    if ($event['ReceiptStatus']) {
                        list($status) = nc_payment_receipt_admin_helpers::get_status_description_from_string($event['ReceiptStatus']);
                        echo $status;
                    }
                ?>
            </td>
            <td class="nc-text-right">
                <? if ($event['AdditionalData']): ?>
                    <a href="#" class="nc-payment-register-log-details-link nc--nowrap">
                        <?= NETCAT_MODULE_PAYMENT_ADMIN_LOG_SHOW_DETAILS ?>
                    </a>
                <? endif; ?>
            </td>
        </tr>
        <? if ($event['AdditionalData']): ?>
            <tr class="nc-bg-lighten nc-payment-register-log-details" style="display: none">
                <td colspan="<?= $show_receipt_link ? 5 : 4 ?>">
                    <pre style="max-width:1200px; overflow-y: auto;"><?php
                        $data = unserialize($event['AdditionalData']) ?: $event['AdditionalData'];
                        if (!$nc_core->NC_UNICODE) {
                            $data = is_array($data)
                                        ? $nc_core->utf8->array_utf2win($data)
                                        : $nc_core->utf8->utf2win($data);
                        }
                        echo htmlspecialchars(var_export($data, true));
                    ?></pre>
                </td>
            </tr>
        <? endif; ?>
    <? endforeach; ?>
</table>

<script>
$nc(function() {
    $nc('.nc-payment-register-log-details-link').click(function() {
        $nc(this).closest('tr').next('.nc-payment-register-log-details').toggle();
        return false;
    });
});
</script>