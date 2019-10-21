<?php

require_once '../../../connect_io.php';
$nc_core = nc_core::get_object();
$nc_core->modules->load_env();

// проверка cron_key
$cron_key = $nc_core->get_settings('SecretKey');
if ($nc_core->input->fetch_get('cron_key') !== $cron_key) {
    die('Invalid cron_key');
}

if (nc_module_check_by_keyword('payment', false)) {
    nc_payment_register::execute_cron_tasks();
}
