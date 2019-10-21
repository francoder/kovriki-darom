<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var nc_payment_receipt $receipt */
/** @var string $register_provider_name */

$id = $receipt->get_id();

$message = sprintf(
    NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_RESEND_CONFIRMATION,
    nc_payment_receipt_admin_helpers::get_receipt_link_by_id($id),
    nc_payment_receipt_admin_helpers::get_register_provider_string($receipt)
);

echo $ui->alert->info($message);

?>
<form method="post">
    <input type="hidden" name="controller" value="receipt">
    <input type="hidden" name="action" value="resend">
    <input type="hidden" name="id" value="<?= $id ?>">
</form>
