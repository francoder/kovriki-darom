<?

    if (isset($_GET['phase']) && $_GET['phase'] == 'print') {

        // Данные ИМ из настроек

        // Название
        if ($form->from_fullname == '&nbsp;') {
            $form->from_fullname = $netshop->get_setting('CompanyName');
        }

        // Адрес строка 1
        if ($form->from_address_line1 == '&nbsp;') {
            $form->from_address_line1 = $netshop->get_setting('Address');
        }

        // Телефон
        if ($form->from_phone == '&nbsp;') {
            $form->from_phone = $netshop->get_setting('Phone');
        }


        // Данные получателя из заказа

        // ФИО
        if ($order->ContactName != '&nbsp;') {
            $form->to_fullname = $order->ContactName;
        }

        // Адрес строка 1
        if ($order->Address != '&nbsp;') {
            $form->to_address_line1 = $order->Address;
        }

        // Адрес строка 2
        if ($form->to_address_line2 == '&nbsp;') {
            $form->to_address_line2 = (!empty($order->Zip) ? $order->Zip .', ' : '' ) .
                'г.' . $db->get_var("SELECT Region_Name FROM Classificator_Region WHERE Region_ID = ".$order->City);
        }

        // Телефон
        if ($order->Phone != '&nbsp;') {
            $form->to_phone = $order->Phone;
        }


        // Стоимость
        $form->value = ceil($form->value);

        if ($form->value) {
            $value = $form->value;
        } else {
            $value = 0;

            foreach ($order_items as $product) {
                $value += $product['TotalPrice'];
            }
        }

        if ($value) {
            $form->value = number_format($value, 0, '', ' ');
            $form->value .= ' руб. /<br/> ' . $form->value . ' RUB';
            if (!$nc_core->NC_UNICODE) {
                $form->value = $nc_core->utf8->utf2win($form->value);
            }
        }

        // Вес
        $form->weight = round(str_replace(',', '.', $form->weight), 3);
        $form->weight = number_format($form->weight, 3, '.', ' ');
    }

?><style type="text/css">
    html, body, div, img { margin: 0; padding: 0; border: 0; font-size: 100%; font: inherit; vertical-align: baseline; }
    body { line-height: 1; font: italic bold 18px "Times New Roman"; }

    #wrapper { position: relative; }
    #wrapper .field { font: italic bold 18px/18px Arial; position: absolute; overflow: visible; height: 18px; color: #000; }

    #wrapper .from-fullname { left: 70px; top: 230px; width: 330px;  }
    #wrapper .from-address-line1 { left: 70px; top: 258px; width: 330px; }
    #wrapper .from-address-line2 { left: 70px; top: 284px; width: 330px; }
    #wrapper .from-phone { left: 140px; top: 312px; width: 150px; }

    #wrapper .russia { left: 350px; top: 312px; width: 100px; }

    #wrapper .to-fullname { left: 460px; top: 230px; width: 330px;  }
    #wrapper .to-address-line1 { left: 460px; top: 258px; width: 330px; }
    #wrapper .to-address-line2 { left: 460px; top: 284px; width: 330px; }
    #wrapper .to-phone { left: 530px; top: 312px; width: 150px; }

    #wrapper .description { left: 60px; top: 387px; height: 100px; width: 240px; line-height: 28px; }
    #wrapper .value { left: 205px; top: 558px; width: 110px; font-size: 14px; line-height: 16px; }
    #wrapper .weight { left: 94px; top: 555px; width: 50px; }

    #wrapper input { width: 330px; height: 20px; line-height: 20px; margin: 0; padding: 0 5px; outline: none; }
    #wrapper input[name='from_phone'],
    #wrapper input[name='to_phone'] { width: 170px; }
    #wrapper input[name='description'] { width: 240px; }
    #wrapper input[name='value'] { width: 110px; }
    #wrapper input[name='weight'] { width: 48px; }
</style>

<div id="wrapper">
    <img src="<?php echo $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>modules/netshop/forms/templates/ems_1.png" width="965" height="738" alt="EMS бланк международного отправления"/>
    <div class="field from-fullname"><?= $form->from_fullname ?></div>
    <div class="field from-address-line1"><?= $form->from_address_line1 ?></div>
    <div class="field from-address-line2"><?= $form->from_address_line2 ?></div>
    <div class="field from-phone"><?= $form->from_phone ?></div>
    <div class="field russia">Russia</div>
    <div class="field to-fullname"><?= $form->to_fullname ?></div>
    <div class="field to-address-line1"><?= $form->to_address_line1 ?></div>
    <div class="field to-address-line2"><?= $form->to_address_line2 ?></div>
    <div class="field to-phone"><?= $form->to_phone ?></div>
    <div class="field description"><?= $form->description ?></div>
    <div class="field value"><?= $form->value ?></div>
    <div class="field weight"><?= $form->weight ?></div>
</div>