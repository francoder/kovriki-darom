<?php

include_once '../../../vars.inc.php';
include_once '../../connect_io.php';
include_once '../default/function.inc.php';
include_once '../netshop/function.inc.php';
include_once '../netshop/ru_utf8.lang.php';
include_once '../../../netcat/modules/payment/ru_cp1251.lang.php';
include_once '../../../netcat/modules/payment/function.inc.php';


$nc_core = nc_Core::get_object();
$netshop = nc_netshop::get_instance();

$context = $netshop->get_condition_context();
$input   = nc_core('input');


// prepare order data
$order_data = array();
foreach ((array)$input->fetch_post('form_data') as $field) {
  $order_data[$field['name']] = $field['value'];
}

// make fake order object and estimate delivery cost for it

$order = nc_netshop_order::from_post_data($order_data, $netshop);

$savedOrder = $netshop->check_new_order($order);

$payment_method = $order->get_payment_method();

$payment_system_id = $payment_method['handler_id'];

$amount = 0;

$txtAmount = ' заказа ';

$deliveryPrice = 0;
$method = $order->get_delivery_estimate();


$deliveryPrice = $method->get('price');

/*
$estimate = $method->get_estimate($order);

if($estimate->get('price') == 100) {
  $deliveryPrice = $method->get('extra_charge_absolute');
} else {
  $deliveryPrice = $estimate->get('price');
}

var_dump($method->get('extra_charge_absolute'));

*/

if($payment_method->get('id') ==3 ) {
  $amount = $payment_method->get_extra_cost($order);
  $txtAmount = ' наложенный платеж ';
} else {
  $amount = $order->get_totals() + $deliveryPrice;
}


if (empty($savedOrder)
    && null != $order->get('ContactName')
    && null != $order->get('Email')
    && null != $order->get('Phone')
    && null != $order->get('City')) {

  $order->save();



  if ($payment_system_id && nc_module_check_by_keyword('payment', false)) {

    $invoice = new nc_payment_invoice(array(

      "payment_system_id" => $payment_system_id,

      "amount" => (float)$amount + $deliveryPrice,

      "description" => "Оплата " . $txtAmount . $order->get_id(),

      "currency" => $netshop->get_currency_code(),

      "customer_id" => 1,

      "customer_name" => $order->get('ContactName'),

      "customer_email" => $order->get('Email'),

      "customer_phone" => $order->get('Phone'),

      "order_source" => 'netshop',

      "order_id" => $order->get_id(),

    ));


    $payment_method = nc_payment_factory::create('nc_payment_system_yandexcpp');

    $invoice->save();

    $netshop->place_order($order);



    $paymentUrl = $payment_method->get_request_form($invoice, false, false);


    //$paymentUrl = 'https://dev.kovriki-darom.ru/netcat/modules/payment/pay_request.php?payment_system=4&invoice='.$invoice->get('id');


  }

  $result = array('error' => 0, 'invoice_url' => $paymentUrl);
  echo nc_array_json($result);

} else {
  $result = array('msg' => implode($savedOrder, ''), 'error' => 1);
  echo nc_array_json($result);
}


