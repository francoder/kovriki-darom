<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";

global $nc_core, $current_user, $AUTH_USER_ID;





if ((mb_strlen($_GET['number'],'UTF-8')) >= 1) {
        $number = $_GET['number'];
    } else {
        $number = "Номер не указан";
    }

   if(isset($_COOKIE["User_ID"])) {
        $User_ID = $_COOKIE["User_ID"];
    } else {
        $User_ID = 1;
    }

    $date = date( "Y-m-d H:i:s");


    
    $requizits = $_GET['requizits'];
    $paytype = $_GET['paytype'];
    $summ = $_GET['summ'];
    $User_Name = $nc_core->db->get_var("SELECT Login FROM User WHERE User_ID = '".$User_ID."'");
    
    $balanceC = $nc_core->db->get_var("SELECT balance FROM User WHERE User_ID = '".$User_ID."'");

    $db->query("UPDATE User SET balance = ".intval($balanceC - $summ)." WHERE User_ID = '".$User_ID."'");


    $prms = "'". $User_ID . "', ".intval($summ).", 'Ожидается подтверждения', '" . $requizits . "', '".$number."', '".$date."'";


      $db->query("INSERT INTO payList (User_ID, summ, status, reqvisits, number, payDate)
VALUES (".$prms.");");





$nc_core->db->query("SELECT * FROM payList ORDER BY id DESC", ARRAY_A );
$payList = $nc_core->db->last_result;












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









    $resp_user = "2114233";






    $contacts['add']=array(
        array(
          'name' => $User_Name,
          'responsible_user_id' => $resp_user,
          'created_by' => $resp_user,
          'created_at' => $date,
          'custom_fields' => array(
              array(
                'id' => "387743",
                  'values' => [
                      array(
                          'value' => $User_ID
                      )
                  ]
              ),
              array(
                'id' => 164197,
                'values' => array(
                   array(
                      'value' => $number,
                      'enum' => 'MOB'
                   )
                )
              )
          )
        )
    );


    $link='https://'.$subdomain.'.amocrm.ru/api/v2/contacts';
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
    curl_setopt($curl,CURLOPT_URL,$link);
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($contacts));
    curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
    curl_setopt($curl,CURLOPT_HEADER,false);
    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

    $out=curl_exec($curl);
    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
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
     if($code!=200 && $code!=204) {
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
      }
    }
    catch(Exception $E)
    {
      die('12: '.$E->getMessage().PHP_EOL.'1234: '.$E->getCode());
    }

    $Response=json_decode($out,true);

    
    $uID = $Response['_embedded']['items'][0]['id'];




 $leads['add']= array(
      array(
        'name'=>'Запрос на вывод средств №'.($payList[0]['id']),
        'created_at'=> $date,
        'status_id'=>18505321,
        'sale'=>$price,
        'responsible_user_id'=>$resp_user,
        'tags' => 'вывод', #Теги
        'contacts_id'  => [
            $uID
        ],
        'custom_fields' => array(
           array(
              'id' => "409765",
              'values' => [
                 array(
                    'value' => $summ
                 )
              ]
           ),
           array(
              'id' => "391963",
              'values' => [
                 array(
                    'value' => $User_ID
                 )
              ]
           ),
           array(
              'id' => "410381",
              'values' => [
                 array(
                    'value' => ($payList[0]['id'])
                 )
              ]
           )
            
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

    
    
    
    
    
    $vopros = "Сумма на вывод: ".$summ." р. Реквизиты для вывода: ".$requizits." . Вывод на: ".$paytype;
    
    
    
    $notes['add'][0] = array(
      'element_id' => $Response["_embedded"] ["items"][0]["id"],
      'element_type' => 2,
      'note_type' => 4,
      'text' => $vopros,
      'created_at' => $date,
      'responsible_user_id' => $resp_user,
      'created_by' => $resp_user
    );

     $link='https://'.$subdomain.'.amocrm.ru/api/v2/notes';
    /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о работе с этой
    библиотекой Вы можете прочитать в мануале. */
    $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
    #Устанавливаем необходимые опции для сеанса cURL
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
    curl_setopt($curl,CURLOPT_URL,$link);
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($notes));
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
     if($code!=200 && $code!=204)
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',(int)$code);
    }
    catch(Exception $E)
    {
      die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
    }
    
    


?>