<?php
/*=========== Skylab interactive - 1.1.2 ========================*/
$NETCAT_FOLDER = join(strstr(__FILE__, "/") ? "/" : "\\", array_slice(preg_split("/[\/\\\]+/", __FILE__), 0, -4)) . (strstr(__FILE__, "/") ? "/" : "\\");
require_once $NETCAT_FOLDER . "vars.inc.php";
require_once $ADMIN_FOLDER . "function.inc.php";
require_once nc_module_folder('sitesecure') . "admin.inc.php";
require_once $ADMIN_FOLDER . "modules/ui.php";
require_once nc_module_folder('sitesecure') . "ui_config.php";

if (!$view) {
    $view = "main";
}

$Title1 = NETCAT_MODULES;
$Title2 = SKYLAB_MODULE_SITESECURE;

BeginHtml($Title2, $Title1, "https://" . $DOC_DOMAIN . "/settings/modules/sitesecure/", 'sitesecure');
echo "<link rel=\"stylesheet\" href=\"" . nc_module_path('sitesecure') . "sitesecure.css\">";
$perm->ExitIfNotAccess(NC_PERM_MODULE, 0, 0, 0, 1);
$UI_CONFIG = new ui_config_module_sitesecure($view, '');

switch ($view) {
    case "main":
        $UI_CONFIG->add_main_toolbar();
        require_once nc_module_folder('sitesecure') . 'page_main.php';
        break;
    case "create":
        require_once nc_module_folder('sitesecure') . 'page_create.php';
        break;
    case "alerts":
        $UI_CONFIG->add_main_toolbar();
        require_once nc_module_folder('sitesecure') . 'page_alerts.php';
        break;
    case "seal":
        $UI_CONFIG->add_main_toolbar();
        require_once nc_module_folder('sitesecure') . 'page_seal.php';
        break;
    case "settings":
        require_once nc_module_folder('sitesecure') . 'page_settings.php';
        break;
    case "info":
        require_once nc_module_folder('sitesecure') . 'page_info.php';
        break;
    case "reply":
        require_once nc_module_folder('sitesecure') . 'page_reply.php';
        break;
}

EndHtml();
