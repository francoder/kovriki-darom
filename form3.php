<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

if ((isset($_GET['number'])) && ((mb_strlen($_GET['number'], 'UTF-8')) >= 4)) {


  $name       = $_GET['name'];
  $number     = $_GET['number'];
  $email      = $_GET['email'];
  $adress     = $_GET['adress'];
  $promo      = $_GET['promo'];
  $delivname  = $_GET['delivname'];
  $payname    = $_GET['payname'];
  $ord_number = $_GET['ord_number'];
  $dosttype   = $_GET['dosttype'];
  $carName    = $_GET['carName'];
  $goodNum    = $_GET['goodNum'];
  $price      = $_GET['price'];
  $post_index = $_GET['port_index'];

  function clean_string($string)
  {
    $bad = array("content-type", "bcc:", "to:", "cc:", "href");

    return str_replace($bad, "", $string);
  }

  $email_to      = "";
  $email_from    = "zakaz@kovriki-darom.ru";
  $email_subject = "Новый заказ №".clean_string($ord_number);


  $email_message .= "Номер заказа: <b>".clean_string($ord_number)."</b><br><br>";

  $email_message .= "ФИО:<b> ".clean_string($name)."</b><br>";
  $email_message .= "Номер телефона: <b>".clean_string($number)."</b><br>";
  $email_message .= "Email: ".clean_string($email)."<br>";
  $email_message .= "Адрес: ".clean_string($adress)."<br>";
  $email_message .= "Промокод: ".clean_string($promo)."<br>";
  $email_message .= "Тип доставки: <b>".clean_string($delivname)."</b><br>";
  $email_message .= "Способ оплаты: <b>".clean_string($payname)."</b><br>";
  if (intval($dosttype) == 2) {
    $email_message .= "Почтовый индекс: ".clean_string($post_index)."<br>";
  } elseif (intval($dosttype) == 1) {
    //$email_message .= "Серия номер паспорта: ".clean_string($passport)."<br>";
  }

  $email_message .= "Товар: ".clean_string($carName)." ".$goodNum." / шт.<br>";
  $email_message .= "Конечная цена: ".clean_string($price)."<br>";


  $mail          = new PHPMailer(true);
  $mail->CharSet = 'UTF-8';
  //$mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Host      = 'localhost';
  $mail->SMTPAuth = false;
  $mail->Username = 'zakaz@kovriki-darom.ru';
  $mail->Password = 'cOde1212';

  $mail->setFrom('zakaz@kovriki-darom.ru', 'Kovriki-Darom');

  $mail->addAddress("evakovrikoff@yandex.ru");

  $mail->isHTML(true);
  $mail->Subject = $email_subject;
  $mail->Body    = $email_message;

  $mail->send();


  ///сообщение клиенту

  $email_to      = clean_string($email);
  $email_from    = "zakaz@kovriki-darom.ru";
  $email_subject = "Номер заказ №".clean_string($ord_number);

  $email_message = "";

  $email_message .= "<div style='font-size: 20px; font-weight: 700'>Спасибо, ваш заказ принят</div>";


  $email_message .= "<br>Номер вашего заказа: <b>".clean_string($ord_number)."</b><br><br>";

  $email_message .= "ФИО:<b> ".clean_string($name)."</b><br>";
  $email_message .= "Номер телефона: <b>".clean_string($number)."</b><br>";
  $email_message .= "Email: ".clean_string($email)."<br>";
  $email_message .= "Адрес: ".clean_string($adress)."<br>";
  $email_message .= "Промокод: ".clean_string($promo)."<br>";
  $email_message .= "Тип доставки: <b>".clean_string($delivname)."</b><br>";
  $email_message .= "Способ оплаты: <b>".clean_string($payname)."</b><br>";
  if (intval($dosttype) == 2) {
    $email_message .= "Почтовый индекс: ".clean_string($post_index)."<br>";
  } elseif (intval($dosttype) == 1) {
    //$email_message .= "Серия номер паспорта: ".clean_string($passport)."<br>";
  }

  $email_message .= "Товар: ".clean_string($carName)." ".$goodNum." / шт.<br>";
  $email_message .= "Конечная цена: ".clean_string($price)."<br>";

  if ($payname == "Яндекс.Деньги") {
    $eee = "PC";
  } else {
    $eee = "AC";
  }

  $email_message .= '<br>Если Вы еще не оплатили Ваш заказ - <form method="POST" class="payforrm" action="https://money.yandex.ru/quickpay/confirm.xml" style="display: inline-block;">
        <input type="hidden" name="receiver" value="410013403004640">
        <input type="hidden" name="formcomment" value="">
        <input type="hidden" name="short-dest" value="Покупка аксессуара Kovriki-Darom.ru">
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



  $mail = new PHPMailer;
  $mail->CharSet = 'UTF-8';
  //$mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Host = 'localhost';
  $mail->SMTPAuth = true;
  $mail->Username = 'zakaz@kovriki-darom.ru';
  $mail->Password = 'cOde1212';
  $mail->SMTPSecure = 'ssl';
  $mail->Port = 587;
  $mail->setFrom('zakaz@kovriki-darom.ru', 'Kovriki-Darom');

  $mail->addAddress($email_to);

  $mail->isHTML(true);
  $mail->Subject = $email_subject;
  $mail->Body    = $email_message;

  $mail->send();



  if ((mb_strlen($_GET['name'], 'UTF-8')) >= 1) {
    $name = $_GET['name'];
  } else {
    $name = "Имя не указанно";
  }


  $number = $_GET['number'];


  $user      = array(
    'USER_LOGIN' => 'evakovrikoff@yandex.ru',
    'USER_HASH'  => '109732db776b857667be1a21e1d372ad',
  );
  $subdomain = 'kovrikidarom';
  $link      = 'https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
  curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $out  = curl_exec($curl);
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  $code   = (int)$code;
  $errors = array(
    301 => 'Moved permanently',
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
  );


  $Response = json_decode($out, true);


  $Response = $Response['response'];


  $resp_user = "2114233";


  $date = new DateTime();
  $date = $date->getTimestamp();


  $contacts['add'] = array(
    array(
      'name'                => $name,
      'responsible_user_id' => $resp_user,
      'created_by'          => $resp_user,
      'created_at'          => $date,
      'custom_fields'       => array(


        array(
          'id'     => 164197,
          'values' => array(
            array(
              'value' => $number,
              'enum'  => 'MOB',
            ),
          ),
        ),
      ),
    ),
  );


  $link = 'https://'.$subdomain.'.amocrm.ru/api/v2/contacts';
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
  curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

  $out    = curl_exec($curl);
  $code   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $code   = (int)$code;
  $errors = array(
    301 => 'Moved permanently',
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
  );

  try {
    if ($code != 200 && $code != 204) {
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    }
  } catch (Exception $E) {
    die('12: '.$E->getMessage().PHP_EOL.'1234: '.$E->getCode());
  }

  $Response = json_decode($out, true);


  $uID = $Response['_embedded']['items'][0]['id'];


  $leads['add'] = array(
    array(
      'name'                => 'Заказ №'.clean_string($ord_number),
      'created_at'          => $date,
      'status_id'           => 18006568,
      'sale'                => 0,
      'responsible_user_id' => $resp_user,
      'tags'                => 'С сайта', #Теги
      'contacts_id'         => [
        $uID,
      ],
    ),
  );
  #Формируем ссылку для запроса
  $link = 'https://'.$subdomain.'.amocrm.ru/api/v2/leads';
  /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о работе с этой
  библиотекой Вы можете прочитать в мануале. */
  $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
  #Устанавливаем необходимые опции для сеанса cURL
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
  $code   = (int)$code;
  $errors = array(
    301 => 'Moved permanently',
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
  );
  try {
    #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    if ($code != 200 && $code != 204) {
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    }
  } catch (Exception $E) {
    die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
  }


  $Response = json_decode($out, true);


  $vopros .= "Номер заказа: ".clean_string($ord_number)."\n";

  $vopros .= "ФИО: ".clean_string($name)."\n";
  $vopros .= "Номер телефона: ".clean_string($number)."\n";
  $vopros .= "Email: ".clean_string($email)."\n";
  $vopros .= "Адрес: ".clean_string($adress)."\n";
  $vopros .= "Промокод: ".clean_string($promo)."\n";
  $vopros .= "Сообщение: ".clean_string($message)."\n";
  $vopros .= "Тип доставки: ".clean_string($delivname)."\n";
  $vopros .= "Способ оплаты: ".clean_string($payname)."\n";
  if (intval($dosttype) == 2) {
    $vopros .= "Почтовый индекс: ".clean_string($post_index)."\n";
  } elseif (intval($dosttype) == 1) {
    $vopros .= "Серия номер паспорта: ".clean_string($passport)."\n";
  }

  $vopros .= "Товар: ".clean_string($carName)." ".$goodNum." / шт.\n";
  $vopros .= "Конечная цена: ".clean_string($price)."\n";


  $notes['add'][0] = array(
    'element_id'          => $Response["_embedded"] ["items"][0]["id"],
    'element_type'        => 2,
    'note_type'           => 4,
    'text'                => $vopros,
    'created_at'          => $date,
    'responsible_user_id' => $resp_user,
    'created_by'          => $resp_user,
  );

  $link = 'https://'.$subdomain.'.amocrm.ru/api/v2/notes';
  /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о работе с этой
  библиотекой Вы можете прочитать в мануале. */
  $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
  #Устанавливаем необходимые опции для сеанса cURL
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($notes));
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
  $code   = (int)$code;
  $errors = array(
    301 => 'Moved permanently',
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
  );
  try {
    #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    if ($code != 200 && $code != 204) {
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', (int)$code);
    }
  } catch (Exception $E) {
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


