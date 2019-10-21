<?php

$NETCAT_FOLDER = realpath(__DIR__ . '/../../../') . '/';
require_once $NETCAT_FOLDER . 'vars.inc.php';
require $INCLUDE_FOLDER . 'index.php';

// Регистрация слушателя событий для netshop, если этот модуль включён
if (nc_module_check_by_keyword('netshop')) {
    $netshop = nc_netshop::get_instance();
    /** @var nc_event $event */
    $event = nc_core('event');
    $event->bind($netshop, array(nc_payment_system::EVENT_ON_PAY_SUCCESS => 'on_payment_success_event_handler'));
    $event->bind($netshop, array(nc_payment_system::EVENT_ON_PAY_FAILURE => 'on_payment_failure_event_handler'));
    $event->bind($netshop, array(nc_payment_system::EVENT_ON_PAY_REJECTED => 'on_payment_rejected_event_handler'));
}

// Собственно обработка ответа

/** @var nc_input $input */
$input = nc_core('input');
$payment_system_class = $input->fetch_get('paySystem');

$payment = nc_payment_factory::create($payment_system_class);

if ($payment) {
    $payment->process_callback_response($input->fetch_get_post());
}

