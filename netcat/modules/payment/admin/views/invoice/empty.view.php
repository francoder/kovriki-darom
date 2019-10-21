<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */

echo $ui->controls->site_select($site_id);
echo $ui->alert->info(NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_EMPTY_LIST);
