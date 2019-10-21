<?php

/**
 * Поиск в индексе товаров.
 *
 * Входящие параметры:
 *   terms — символы для поиска
 *   limit — максимальное количество результатов
 *   order_data (опционально) — информация о заказе (основные поля — f_*, корзина — элемент items)
 *   site_id
 */

$NETCAT_FOLDER = realpath(__DIR__ . '/../../../../../') . '/';
require_once $NETCAT_FOLDER . 'vars.inc.php';
require_once $ADMIN_FOLDER . 'function.inc.php';

$nc_core = nc_core::get_object();

$terms = $nc_core->input->fetch_get_post('terms');
$site_id = $nc_core->input->fetch_get_post('site_id');
$limit = $nc_core->input->fetch_get_post('limit');

$netshop = nc_netshop::get_instance($site_id);

$items = $netshop->itemindex->find($terms, $limit);

$properties = array(
    'Class_ID', 'Message_ID', 'RowID',
    'Article', 'FullName', 'URL',
    'OriginalPrice', 'OriginalPriceF',
    'ItemDiscount', 'Discounts',
    'ItemPrice', 'ItemPriceF',
    'Units',
);

if (!$netshop->get_setting('IgnoreStockUnitsValue')) {
    $properties[] = 'StockUnits';
}

$i = 0;
$result = array();

foreach ($items as $item) {
    foreach ($properties as $k) {
        $result[$i][$k] = $item[$k];
    }
    $i++;
}

header('Content-Type: application/json');
echo nc_array_json($result);
