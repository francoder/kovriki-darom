<?php
if((isset($_POST['number'])) && ((mb_strlen($_POST['number'],'UTF-8')) >= 4)) {

    $email_to = "evakovrikoff@yandex.ru";
    $email_from = "zvonok@kovriki-darom.ru";
    $email_subject = "Заявка на звонок Оптовая продажа ".$_SERVER['SERVER_NAME'];

    $name = $_POST['name'];
    $number = $_POST['number'];
    $city = $_POST['city'];

    function clean_string($string) {
        $bad = array("content-type","bcc:","to:","cc:","href");
        return str_replace($bad,"",$string);
    }



    $email_message .= "Имя: ".clean_string($name)."\n";
    $email_message .= "Номер телефона: ".clean_string($number)."\n";
    $email_message .= "Город: ".clean_string($city)."\n";

    // create email headers
    $headers = 'From: '.$email_from."\r\n".
        'Reply-To: '.$email_from."\r\n" .
        'X-Mailer: PHP/' . phpversion();
    @mail($email_to, $email_subject, $email_message, $headers);


    if ((mb_strlen($_POST['name'],'UTF-8')) >= 1) {
        $name = $_POST['name'];
    } else {
        $name = "Имя не указанно";
    }


    $number = $_POST['number'];



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
            'name'=>'Заявка на звонок Оптовая продажа',
            'created_at'=> $date,
            'status_id'=>18006568,
            'sale'=>0,
            'responsible_user_id'=>$resp_user,
            'tags' => 'Позвонить', #Теги
            'contacts_id'  => [
                $uID
            ]
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
    function func() {
        window.history.back();
    }
    setTimeout(func, 3000);
</script>

<body>
</body>

</html>