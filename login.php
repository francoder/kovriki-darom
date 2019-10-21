<?  
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(1200);


require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";

global $nc_core, $current_user, $AUTH_USER_ID;

$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_GET['token'] . '&host=' . $_SERVER['HTTP_HOST']);

$user = json_decode($s, true);
//$user['network'] - соц. сеть, через которую авторизовался пользователь
//$user['identity'] - уникальная строка определяющая конкретного пользователя соц. сети
//$user['first_name'] - имя пользователя
//$user['last_name'] - фамилия пользователя
setcookie("last_name",$user["last_name"], time()+3600000);
setcookie("first_name",$user["first_name"], time()+3600000);
setcookie("photo",$user["photo_big"], time()+3600000);
setcookie("uid",$user["uid"], time()+3600000);
setcookie("identity",$user["identity"], time()+3600000);
setcookie("first_name",$user["first_name"], time()+3600000);




// добавить нового пользователя

$settings = $db->get_row("SELECT * FROM User WHERE identity='".$user["identity"]."'", ARRAY_A);


if(!count($settings)) {
    $user_id = $nc_core->user->add( array('Login' => $user["first_name"].' '.$user["last_name"], 'Email' => ''), '1,3' , '123123123', array('Checked' => '1') );
    
    
    $prms = "socialID = ".$user["uid"].", identity = '".$user["identity"]."', photo = '".$user["photo_big"]."', network = '".$user["network"]."'";
    
    if(isset($_COOKIE['r'])) {
        $prms .= ", referralParent = '".$_COOKIE['r']."', referralLink = '".$user_id."'";
    }
    
    
    
    $db->query("UPDATE User SET ".$prms."  WHERE User_ID = '".$user_id."'");
    
    
    $settings = $db->get_row("SELECT * FROM User WHERE User_ID='".$user_id."'", ARRAY_A);
    $db->query("UPDATE User SET referralLink = '".$settings["User_ID"]."'  WHERE User_ID = '".$settings["User_ID"]."'");

}


setcookie("User_ID",$settings["User_ID"], time()+3600000);
setcookie("r",$settings["referralParent"], time()+3600000);


echo json_encode($user);
?>