<?php
require '../../../no_header.inc.php';

/**
 * ShopStatus classifier values as JSON
 */

/* @var nc_db $db */
$db = nc_core('db');

/** @var nc_input $input */
$classifier = "ShopOrderStatus";
$table = "Classificator_$classifier";

$rows = $db->get_results("SELECT `{$classifier}_ID`, `{$classifier}_Name`
                            FROM `$table`
                           WHERE `Checked` = 1
                          ORDER BY `{$classifier}_Priority`",
    ARRAY_A);

$result = array(NETCAT_MODULE_NETSHOP_ORDER_NEW); // отдельный файл существует только из-за этой строчки...

foreach ($rows as $row) {
    $result[$row["{$classifier}_ID"]] = $row["{$classifier}_Name"];
}

echo nc_netshop_condition_admin_helpers::key_value_json($result);