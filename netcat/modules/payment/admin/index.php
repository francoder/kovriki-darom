<?php

$NETCAT_FOLDER = realpath(__DIR__ . "/../../../../") . "/";

require_once($NETCAT_FOLDER . "vars.inc.php");
require_once($ADMIN_FOLDER . "function.inc.php");

if (is_file($MODULE_FOLDER . "payment/" . MAIN_LANG . ".lang.php")) {
    require_once($MODULE_FOLDER . "payment/" . MAIN_LANG . ".lang.php");
} else {
    require_once($MODULE_FOLDER . "payment/en.lang.php");
}

require_once($SYSTEM_FOLDER . '/admin/ui/components/nc_ui_controller.class.php');

const CONTROLLER_PREFIX = 'nc_payment_';
const CONTROLLER_POSTFIX = '_admin_controller';

$controller_name_short = nc_core::get_object()->input->fetch_post_get('controller');
$action = nc_core::get_object()->input->fetch_post_get('action');
$controller_name = CONTROLLER_PREFIX . $controller_name_short . CONTROLLER_POSTFIX;
$views_path = __DIR__ . '/views/' . $controller_name_short;

$controller = new $controller_name($views_path);
echo $controller->execute($action);
