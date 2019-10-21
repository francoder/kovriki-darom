<html>
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    
    
    <br>Если Вы еще не оплатили Ваш заказ - <form method="POST" class="payforrm" action="https://money.yandex.ru/quickpay/confirm.xml" style="display: inline-block;">
        <input type="hidden" name="receiver" value="410013403004640">
        <input type="hidden" name="formcomment" value="">
        <input type="hidden" name="short-dest" value="Покупка ковриков Kovriki-Darom.ru">
        <input type="hidden" name="label" value="<?=$_GET['order'];?>">
        <input type="hidden" name="quickpay-form" value="donate">
        <input type="hidden" name="targets" value="Оплата заказа №<?=$_GET['order'];?>">
        <input type="hidden" name="sum" value="<?=$_GET['price'];?>" data-type="number">
        <input type="hidden" name="need-fio" value="false">
        <input type="hidden" name="need-phone" value="false">
        <input type="hidden" name="need-address" value="false">
        <input type="hidden" name="paymentType" value="<?=$_GET['type'];?>">
        <input type="submit" value="нажмите на ссылку для оплаты" style="background:none;border: 0px; text-decoration: underline; color: red; cursor: pointer" >
      </form><br>
    
    
    
</body>
</html>