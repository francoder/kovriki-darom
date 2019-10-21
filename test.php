<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(1200);


require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";

global $nc_core, $current_user, $AUTH_USER_ID;

$refAcc = $db->get_results("SELECT * FROM orders WHERE referral = 'Sey' AND paySend > 0");
echo 123;
?>