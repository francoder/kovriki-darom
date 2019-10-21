<?php

require_once '../../../connect_io.php';
$nc_core = nc_core::get_object();
$nc_core->modules->load_env();

$register = nc_payment_register::get_provider_instance();
if ($register) {
    $register->process_callback();
}
