<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . "/vars.inc.php";
require $_SERVER['DOCUMENT_ROOT'] . "/netcat/connect_io.php";

global $nc_core, $current_user, $AUTH_USER_ID;

if((isset($_GET['number'])) && ((mb_strlen($_GET['number'],'UTF-8')) >= 4)) {

    $name = $_GET['name'];
    $number = $_GET['number'];
    $email = $_GET['email'];
    $adress = $_GET['adress'];
    $promo = $_GET['promo'];
    $delivname = $_GET['delivname'];
    $payname = $_GET['payname'];
    $ord_number = $_GET['ord_number'];
    $dosttype = $_GET['dosttype'];
    $complect = $_GET['complect'];
    $carName = $_GET['carName'];
    $version = $_GET['version'];
    $colorName = $_GET['colorName'];
    $borderName = $_GET['borderName'];
    $price = $_GET['price'];
    $post_index = $_GET['port_index'];
    
    $cudate = date("Y-m-d H:i:s");
    
    if(isset($_COOKIE["User_ID"])) {
        $User_ID = $_COOKIE["User_ID"];
    } else {
        $User_ID = 1;
    }
    
    if(isset($_COOKIE["r"])) {
        $referral = $_COOKIE["r"];
    } else {
        $referral = 1;
    }
    
    
   $prms = "'".$_GET['name'] . "', 'В обработке', '" . $_GET['number'] . "', '" . $_GET['email'] . "', '" . $_GET['adress'] . "', '" . $_GET['promo'] . "', '" . $_GET['delivname'] . "', '" . $_GET['payname'] . "', '" . $_GET['ord_number'] . "', '" . strip_tags(nl2br($complect)) . "', '" . $_GET['carName'] . "', '" . $_GET['version'] . "', '" . $_GET['colorName'] . "', '" . $_GET['borderName'] . "', '" . $_GET['price'] . "', '" . $post_index . "', '" . $User_ID . "', '" . $referral . "', '" . $cudate . "'";

    $db->query("INSERT INTO orders (name, status, number, email, adress, promo, delivname, payname, ord_number, complect, carName, version, colorName, borderName, price, post_index, User_ID, referral, Order_date)
VALUES (".$prms.");");
    


    
    if(isset($_COOKIE['roistat_visit'])) {
        $visit = file_get_contents('http://cloud.roistat.com/api/project/1.0/visits/'.$_COOKIE['roistat_visit'].'?secret=4695d45ba2ac80323fc5cf0008c6e87f&project=71807');
        $visit = json_decode($visit);
        $utm_source = $visit->data->utm_source;
        $utm_medium = $visit->data->utm_medium;
        $utm_term = $visit->data->utm_term ;
        $utm_campaign = $visit->data->utm_campaign;
        $utm_content = $visit->data->utm_content;
    } else {
        $utm_source = "";
        $utm_medium = "";
        $utm_term = "";
        $utm_campaign = "";
        $utm_content = "";
    }
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function clean_string($string) {
        $bad = array("content-type","bcc:","to:","cc:","href");
        return str_replace($bad,"",$string);
    }
    
    
    if ($payname == "Яндекс.Деньги") {
        $eee = "PC";
    } else {
        $eee = "AC";
    }

    if ($payname == "Наложенным платежом") {
        $PayLink = 'Ссылка для предоплаты: <a href="kovriki-darom.ru/pay.php?order='.clean_string($ord_number).'&type='.$eee.'&price=500">kovriki-darom.ru/pay.php?order='.clean_string($ord_number).'&type='.$eee.'&price='.clean_string($price).'</a><br>';
    } else {
        $PayLink = 'Ссылка для оплаты: <a href="kovriki-darom.ru/pay.php?order='.clean_string($ord_number).'&type='.$eee.'&price='.clean_string($price).'">kovriki-darom.ru/pay.php?order='.clean_string($ord_number).'&type='.$eee.'&price='.clean_string($price).'</a><br>';
    }
    
    
    
    

    $email_to = "";
    $email_from = "zakaz@kovriki-darom.ru";
    $email_subject = "Новый заказ №".clean_string($ord_number);
 

 

 
    $email_message .= "Номер заказа: <b>".clean_string($ord_number)."</b><br><br>";
 
    $email_message .= "ФИО:<b> ".clean_string($name)."</b><br>";
    $email_message .= "Номер телефона: <b>".clean_string($number)."</b><br>";
    $email_message .= "Email: ".clean_string($email)."<br>";
    $email_message .= "Адрес: ".clean_string($adress)."<br>";
    $email_message .= "Промокод: ".clean_string($promo)."<br>";
    $email_message .= "Сообщение: ".clean_string($message)."<br>";
    $email_message .= "Тип доставки: <b>".clean_string($delivname)."</b><br>";
    $email_message .= "Способ оплаты: <b>".clean_string($payname)."</b><br>";
    if (intval($dosttype) == 2) {
        $email_message .= "Почтовый индекс: ".clean_string($post_index)."<br>";
    } else if (intval($dosttype) == 1) {
        //$email_message .= "Серия номер паспорта: ".clean_string($passport)."<br>";
    }
    
    $email_message .= $PayLink;
    
    $email_message .= "Комплектация: ".clean_string($complect)."<br>";
    $email_message .= clean_string($carName)."<br>";
    $email_message .= "Версия соты: ".clean_string($version)."<br>";
    $email_message .= "Цвет коврика: ".clean_string($colorName)."<br>";
    $email_message .= "Цвет окантовки: ".clean_string($borderName)."<br>";
    $email_message .= "Конечная цена: ".clean_string($price)."<br>";
    
 

    
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    //$mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.beget.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'zakaz@kovriki-darom.ru';
    $mail->Password = 'cOde1212';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('zakaz@kovriki-darom.ru', 'Kovriki-Darom');
    
    $mail->addAddress("evakovrikoff@yandex.ru");
    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body    = $email_message;
    
    $mail->send();













    ///сообщение клиенту

    $email_to = clean_string($email);
    $email_from = "zakaz@kovriki-darom.ru";
    $email_subject = "Номер заказ №".clean_string($ord_number);
    
    $email_message = "";

    $email_message .= "<div style='font-size: 20px; font-weight: 700'>Спасибо, ваш заказ принят</div>";

 
    $email_message .= "<br>Номер вашего заказа: <b>".clean_string($ord_number)."</b><br><br>";
 
    $email_message .= "ФИО:<b> ".clean_string($name)."</b><br>";
    $email_message .= "Номер телефона: <b>".clean_string($number)."</b><br>";
    $email_message .= "Email: ".clean_string($email)."<br>";
    $email_message .= "Адрес: ".clean_string($adress)."<br>";
    $email_message .= "Промокод: ".clean_string($promo)."<br>";
    $email_message .= "Сообщение: ".clean_string($message)."<br>";
    $email_message .= "Тип доставки: <b>".clean_string($delivname)."</b><br>";
    $email_message .= "Способ оплаты: <b>".clean_string($payname)."</b><br>";
    if (intval($dosttype) == 2) {
        $email_message .= "Почтовый индекс: ".clean_string($post_index)."<br>";
    } else if (intval($dosttype) == 1) {
        //$email_message .= "Серия номер паспорта: ".clean_string($passport)."<br>";
    }
    
    $email_message .= "Комплектация: ".clean_string($complect)."<br>";
    $email_message .= clean_string($carName)."<br>";
    $email_message .= "Версия соты: ".clean_string($version)."<br>";
    $email_message .= "Цвет коврика: ".clean_string($colorName)."<br>";
    $email_message .= "Цвет окантовки: ".clean_string($borderName)."<br>";
    $email_message .= "Конечная цена: ".clean_string($price)."<br>";

    if ($payname == "Наложенным платежом") { 
        $email_message .= '<br>Если Вы еще не внесли предоплату за Ваш заказ - <form method="POST" class="payforrm" action="https://money.yandex.ru/quickpay/confirm.xml" style="display: inline-block;">
        <input type="hidden" name="receiver" value="410013403004640">
        <input type="hidden" name="formcomment" value="">
        <input type="hidden" name="short-dest" value="Предоплата ковриков Kovriki-Darom.ru">
        <input type="hidden" name="label" value="'.$ord_number.'">
        <input type="hidden" name="quickpay-form" value="donate">
        <input type="hidden" name="targets" value="Внесение предоплаты по заказу №'.$ord_number.'">
        <input type="hidden" name="sum" value="500" data-type="number">
        <input type="hidden" name="need-fio" value="false">
        <input type="hidden" name="need-phone" value="false">
        <input type="hidden" name="need-address" value="false">
        <input type="hidden" name="paymentType" value="'.$eee.'">
        <input type="submit" value="нажмите на ссылку для оплаты" style="background:none;border: 0px; text-decoration: underline; color: red; cursor: pointer" >
      </form><br>';
    } else {
        $email_message .= '<br>Если Вы еще не оплатили Ваш заказ - <form method="POST" class="payforrm" action="https://money.yandex.ru/quickpay/confirm.xml" style="display: inline-block;">
        <input type="hidden" name="receiver" value="410013403004640">
        <input type="hidden" name="formcomment" value="">
        <input type="hidden" name="short-dest" value="Покупка ковриков Kovriki-Darom.ru">
        <input type="hidden" name="label" value="'.$ord_number.'">
        <input type="hidden" name="quickpay-form" value="donate">
        <input type="hidden" name="targets" value="Оплата заказа №'.$ord_number.'">
        <input type="hidden" name="sum" value="'.$price.'" data-type="number">
        <input type="hidden" name="need-fio" value="false">
        <input type="hidden" name="need-phone" value="false">
        <input type="hidden" name="need-address" value="false">
        <input type="hidden" name="paymentType" value="'.$eee.'">
        <input type="submit" value="нажмите на ссылку для оплаты" style="background:none;border: 0px; text-decoration: underline; color: red; cursor: pointer" >
      </form><br>';
    }
    


    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    //$mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.beget.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'zakaz@kovriki-darom.ru';
    $mail->Password = 'cOde1212';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('zakaz@kovriki-darom.ru', 'Kovriki-Darom');
    
    $mail->addAddress($email_to);
    
    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body    = $email_message;
    
    $mail->send();






    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    if ((mb_strlen($_GET['name'],'UTF-8')) >= 1) {
        $name = $_GET['name'];
    } else {
        $name = "Имя не указанно";
    }

    
    $number = $_GET['number'];



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

    if (isset($_COOKIE['r'])) {
        $whoInvite = $_COOKIE['r'];
    } else {
        $whoInvite = 0;
    }
    
    if (isset($_COOKIE['User_ID'])) {
        $User_ID = $_COOKIE['User_ID'];
    } else {
        $User_ID = 0;
    }



    $date = new DateTime();
    $date = $date->getTimestamp();



    $contacts['add']=array(
        array(
          'name' => $name,
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
                  'id' => "386987",
                  'values' => [
                     array(
                        'value' => $whoInvite
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
              ),
              array(
                'id' => 164199,
                'values' => array(
                   array(
                      'value' => $email,
                      'enum' => 'WORK'
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
        'name'=>'Заказ №'.clean_string($ord_number),
        'created_at'=> $date,
        'status_id'=>18006568,
        'sale'=>$price,
        'responsible_user_id'=>$resp_user,
        'tags' => '', #Теги
        'contacts_id'  => [
            $uID
        ],
        'custom_fields' => array(
            array(
              'id' => "191513",
              'values' => [
                 array(
                    'value' => $utm_source
                 )
              ]
           ),
           array(
              'id' => "191515",
              'values' => [
                 array(
                    'value' => $utm_medium
                 )
              ]
           ),
           array(
              'id' => "191517",
              'values' => [
                 array(
                    'value' => $utm_campaign
                 )
              ]
           ),
           array(
              'id' => "387529",
              'values' => [
                 array(
                    'value' => $utm_term
                 )
              ]
           ),
           array(
              'id' => "191519",
              'values' => [
                 array(
                    'value' => $utm_content
                 )
              ]
           ),
           array(
              'id' => "386667",
              'values' => [
                 array(
                    'value' => $adress
                 )
              ]
           ),
           array(
              'id' => "386681",
              'values' => [
                 array(
                    'value' => $promo
                 )
              ]
           ),
           array(
              'id' => "386697",
              'values' => [
                 array(
                    'value' => $delivname
                 )
              ]
           ),
           array(
              'id' => "386699",
              'values' => [
                 array(
                    'value' => $payname
                 )
              ]
           ),
           array(
              'id' => "386737",
              'values' => [
                 array(
                    'value' => $ord_number
                 )
              ]
           ),
           array(
              'id' => "391115",
              'values' => [
                 array(
                    'value' => $version
                 )
              ]
           ),
           array(
              'id' => "386763",
              'values' => [
                 array(
                    'value' => $carName
                 )
              ]
           ),
           array(
              'id' => "386947",
              'values' => [
                 array(
                    'value' => $colorName
                 )
              ]
           ),
           array(
              'id' => "386949",
              'values' => [
                 array(
                    'value' => $borderName
                 )
              ]
           ),
           array(
              'id' => "386959",
              'values' => [
                 array(
                    'value' => $price
                 )
              ]
           ),
           array(
              'id' => "386945",
              'values' => [
                 array(
                    'value' => $post_index
                 )
              ]
           ),
           array(
              'id' => "387587",
              'values' => [
                 array(
                    'value' => $_COOKIE['roistat_visit']
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
              'id' => "391965",
              'values' => [
                 array(
                    'value' => $referral
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

    
    
    
    
    
    $vopros = strip_tags(nl2br($complect));
    
    
    
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
    
    
    
    





}
?>
<!-- include your own success html here -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Помочь</title>
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/main.css">
    <link rel="stylesheet" href="style/response.css">
</head>

    <h1>Спасибо! Мы вам позвоним!</h1>

<script>
    window.history.back()
</script>
<body>
</body>
</html>


