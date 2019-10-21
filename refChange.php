<?
error_reporting(-1);
ini_set('display_errors', 'On');
require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";

global $nc_core, $current_user, $AUTH_USER_ID;


if(isset($_GET["refname"])) {
    
    $oldRef = $nc_core->db->get_var("SELECT referralLink FROM User WHERE User_ID = '".$_COOKIE['User_ID']."'");
    
    $db->query("UPDATE User SET referralLink = '".$_GET["refname"]."' WHERE User_ID = '".$_COOKIE['User_ID']."'");
    $db->query("UPDATE User SET referralParent = '".$_GET["refname"]."' WHERE referralParent = '".$oldRef."'");
    $db->query("UPDATE orders SET referral = '".$_GET["refname"]."' WHERE referral = '".$oldRef."'");
    
    
    
    
    $user=array(
      'USER_LOGIN'=>'evakovrikoff@yandex.ru',
     'USER_HASH'=>'109732db776b857667be1a21e1d372ad'
    );
    $subdomain='kovrikidarom';
    $link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';

    $curl=curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
    curl_setopt($curl,CURLOPT_URL,$link);
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($user));
    curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
    curl_setopt($curl,CURLOPT_HEADER,false);
    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
    $out=curl_exec($curl); 
    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
    curl_close($curl);
    $code=(int)$code;
    $errors=array(
      301=>'Moved permanently',
      400=>'Bad request',
      401=>'Unauthorized',
      403=>'Forbidden',
      404=>'Not found',
      500=>'Internal server error',
      502=>'Bad gateway',
      503=>'Service unavailable'
    );



    $Response=json_decode($out,true);
    $Response=$Response['response'];



    $link='https://'.$subdomain.'.amocrm.ru/api/v2/leads?query='.$oldRef;
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
    curl_setopt($curl,CURLOPT_URL,$link);
    curl_setopt($curl,CURLOPT_HEADER,false);
    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); 
    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($curl,CURLOPT_HTTPHEADER,array('IF-MODIFIED-SINCE: Mon, 01 Aug 2013 07:07:23'));
    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
    curl_close($curl);
    $code=(int)$code;
    $errors=array(
      301=>'Moved permanently',
      400=>'Bad request',
      401=>'Unauthorized',
      403=>'Forbidden',
      404=>'Not found',
      500=>'Internal server error',
      502=>'Bad gateway',
      503=>'Service unavailable'
    );
    try
    {
      /* Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке */
      if($code!=200 && $code!=204) {
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
      }
    }
    catch(Exception $E)
    {
      die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
    }
    $Response=json_decode($out,true);
    var_dump($Response);
    
    if (isset($Response["_embedded"]["items"])) {
        
    
        $Response = $Response["_embedded"]["items"];
    
        foreach($Response as $item) {


            foreach($item["custom_fields"] as $element) {
               


                if (($element["id"] == 391965) && ($element["values"][0]["value"] !== "1")) {








                    $leads['update']= array(
                      array(
                        'id'=> $item["id"],
                        'updated_at' => strtotime("now"),
                        'custom_fields' => array(
                           array(
                              'id' => "391965",
                              'values' => [
                                 array(
                                    'value' => $_GET["refname"]
                                 )
                              ]
                           ),

                        )
                      )
                    );
                    #Формируем ссылку для запроса
                    $link='https://'.$subdomain.'.amocrm.ru/api/v2/leads';
                    /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о работе с этой
                    библиотекой Вы можете прочитать в мануале. */
                    $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
                    #Устанавливаем необходимые опции для сеанса cURL
                    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
                    curl_setopt($curl,CURLOPT_URL,$link);
                    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
                    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($leads));
                    curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
                    curl_setopt($curl,CURLOPT_HEADER,false);
                    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
                    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
                    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
                    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
                    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
                    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
                    /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
                    $code=(int)$code;
                    $errors=array(
                      301=>'Moved permanently',
                      400=>'Bad request',
                      401=>'Unauthorized',
                      403=>'Forbidden',
                      404=>'Not found',
                      500=>'Internal server error',
                      502=>'Bad gateway',
                      503=>'Service unavailable'
                    );
                    try
                    {
                      #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
                     if($code!=200 && $code!=204) {
                        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
                      }
                    }
                    catch(Exception $E)
                    {
                      die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
                    }


                    $Response=json_decode($out,true);


                     echo "<pre>";
                        var_dump($Response); 
                    echo "</pre>";










                }


            
            }


        }
    }
}

?>