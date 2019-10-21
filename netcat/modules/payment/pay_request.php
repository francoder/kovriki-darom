<?php

require '../../connect_io.php';
require $INCLUDE_FOLDER . 'index.php';
$nc_core = nc_core::get_object();
$nc_core->modules->load_env();

$input = $nc_core->input;
$invoice_id = $input->fetch_post_get('invoice_id');
$payment_system_name = $input->fetch_post_get('payment_system');

$is_admin = isset($perm) && $perm instanceof Permission && $perm->isCatalogueAdmin($catalogue);

try {
    $invoice = new nc_payment_invoice($invoice_id);

    if ($invoice->get('status') === nc_payment_invoice::STATUS_SUCCESS) {
        die(NETCAT_MODULE_PAYMENT_ERROR_ALREADY_PAID);
    }

//    if ($invoice->get('customer_id')) {
//        if (!$AUTH_USER_ID) {
//            die(NETCAT_MODULE_PAYMENT_ERROR_AUTH_REQUIRED);
//        }
//
//        if ($AUTH_USER_ID != $invoice->get('customer_id') && !$is_admin) {
//            die(NETCAT_MODULE_PAYMENT_ERROR_WRONG_USER);
//        }
//    }

    $payment_system = nc_payment_factory::create($payment_system_name);
    $params = array();

    foreach ((array)$input->fetch_post_get() as $key => $value) {
        if (strpos($key, 'param_') === 0) {
            $key = substr($key, strlen('param_'));
            $params[$key] = $value;
        }
    }

    $payment_system->set_request_parameters($params);
    $payment_system->process_payment_request($invoice);
}
catch (Exception $e) {
    echo "<div style='max-width:800px; margin:0 auto; font:16px/25px sans-serif'>";
    echo $e->getMessage();

    if ($is_admin && isset($payment_system) && $payment_system instanceof nc_payment_system) {
        echo "<ul><li>" . join("</li>\n<li>", $payment_system->get_errors()) . "</li></ul>";
        $payment_system_id = $payment_system->get_id();
        echo "<p><a href='" . nc_core('ADMIN_PATH') . "#module.payment.system.edit($catalogue,$payment_system_id)' target='_blank'>",
             NETCAT_MODULE_PAYMENT_SETTINGS_LINK,
             "</a></p>";
    }
    echo "</div>";
}