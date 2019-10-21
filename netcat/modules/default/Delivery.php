<?php

include_once '../../../vars.inc.php';
include_once '../../connect_io.php';
include_once '../default/function.inc.php';
include_once '../netshop/function.inc.php';
include_once '../netshop/ru_utf8.lang.php';

$nc_core = nc_Core::get_object();
$netshop = nc_netshop::get_instance();

$context = $netshop->get_condition_context();

$all_delivery_methods = $netshop->delivery->get_enabled_methods();

// разблокируем файл сессии, чтобы запросы на расчёт доставки выполнялись параллельно
session_write_close();

/** @var nc_input $input */
/** @var nc_netshop $netshop */
$input = nc_core('input');
$netshop = nc_modules('netshop'); // this will load language constants

$delivery_method_id = $input->fetch_post('delivery_method_id');

// prepare order data
$order_data = array();
foreach ((array) $input->fetch_post('form_data') as $field) {
  $order_data[$field['name']] = $field['value'];
}

// make fake order object and estimate delivery cost for it
$order = nc_netshop_order::from_post_data($order_data, $netshop);
$estimate = $netshop->delivery->get_estimate($delivery_method_id, $order);
$context->set_order($order);
$available_delivery_methods = $all_delivery_methods->matching($context);

$courier_delivery_methods = $available_delivery_methods->where(
  'delivery_type',
  nc_netshop_delivery::DELIVERY_TYPE_COURIER
);
$pickup_delivery_methods = $available_delivery_methods->where(
  'delivery_type',
  nc_netshop_delivery::DELIVERY_TYPE_PICKUP
);

$city = $order->get_location_name();
$deliveryMethods = [];
$deliveryCourier = [];
$deliveryPickup = [];


foreach ($available_delivery_methods as $method) {
  $name = $method->get('name');
  $description = $method->get('description');

  //var_dump(get_class_methods($estimate));
  //var_dump($delivery_method_id);

  $deliveryMethods[] = [
    'delivery_id' => $method->get_id(),
    'delivery_name' => $name,
    'delivery_description' => $description,
    'delivery_price' => $netshop->format_price($method->get('extra_charge_absolute'))
  ];
}

foreach ($courier_delivery_methods as $courier) {
  $deliveryCourier = $courier->get_delivery_points($city)->to_array(true);
}

foreach ($pickup_delivery_methods as $pickup) {
  if ($pickup->get_id() == $delivery_method_id) {
    $deliveryPickup = $pickup->get_delivery_points($city)->to_array(true);
  }
}

// prepare $result array
if ($estimate->has_error()) {
  $result = array(
    'delivery_method_id' => $delivery_method_id,
    'error' => $estimate->get('error'),
    'error_code' => $estimate->get('error_code'),
    'delivery_methods' => nc_array_json($deliveryMethods),
    'delivery_courier' => nc_array_json($deliveryCourier),
    'delivery_pickup' => nc_array_json($deliveryPickup)
  );
} else {
  $price = $estimate->get('price');
  $discount = $estimate->get('discount');
  $full_price = $estimate->get('full_price');

  $result = array(
    'delivery_method_id' => $delivery_method_id,

    'price' => $price,
    'formatted_price' => $price ? $netshop->format_price($price) : NETCAT_MODULE_NETSHOP_DELIVERY_FREE_OF_CHARGE,

    'full_price' => $full_price,
    'formatted_full_price' => $netshop->format_price($full_price),

    'discount' => $discount,
    'formatted_discount' => $netshop->format_price($discount),

    'formatted_price_and_discount' => $estimate->get_formatted_price_and_discount(),

    'min_days' => $estimate->get('min_days'),
    'max_days' => $estimate->get('max_days'),
    'min_date' => $estimate->get_closest_delivery_date(),
    'max_date' => $estimate->get_latest_delivery_date(),
    'dates_string' => $estimate->get_dates_string(),
    'delivery_methods' => nc_array_json($deliveryMethods),
    'delivery_courier' => nc_array_json($deliveryCourier),
    'delivery_pickup' => nc_array_json($deliveryPickup)
  );
}

echo nc_array_json($result);

?>
