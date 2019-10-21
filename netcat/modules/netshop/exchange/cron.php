<?php

// Подключим ядро
$netcat_folder = dirname(dirname(dirname(__DIR__)));
require $netcat_folder . '/connect_io.php';
$nc_core = nc_core::get_object();
$nc_core->modules->load_env('ru');

if (!function_exists('exit_with_error')) {
    function exit_with_error($error) {
        $message = "ERROR: {$error};";
        echo $message;
        exit;
    }
}

// Проверим данные
$object_id = $nc_core->input->fetch_get('id');
if ($object_id <= 0) {
    exit_with_error('WRONG OBJECT ID');
}
try {
    $object = nc_netshop_exchange_object::by_id($object_id);
} catch (Exception $e) {
    exit_with_error('OBJECT WITH GIVEN ID NOT FOUND');
}
if ($object->get_mode() != nc_netshop_exchange_object::MODE_MANUAL) {
    exit_with_error("THIS OBJECT CAN'T BE USED WITH CRON TASKS (REASON: NOT IN MANUAL MODE)");
}
if (!$object->is_checked()) {
    exit_with_error('THIS OBJECT IS DISABLED');
}
$cron_key = $nc_core->input->fetch_get('key');
if (empty($cron_key)) {
    exit_with_error('CRON KEY NOT FOUND');
}
if ($cron_key != $object->get('cron_key')) {
    exit_with_error('CRON KEY IS WRONG');
}

// Запустим обмен
$exchange_id = $object->run();
echo "OK: {$exchange_id}";
exit;