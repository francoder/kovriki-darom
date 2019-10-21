<?
error_reporting(-1);
ini_set('display_errors', 'On');
require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";

global $nc_core, $current_user, $AUTH_USER_ID;


if(isset($_GET["refname"])) {
    
    $refAcc = $db->get_results("SELECT * FROM User WHERE referralLink = '".$_GET["refname"]."'");
    
    echo count($refAcc);

}

?>