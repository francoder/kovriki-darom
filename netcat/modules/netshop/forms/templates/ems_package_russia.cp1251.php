<?php

   if (isset($_GET['phase']) && $_GET['phase'] == 'print') {

        // Данные ИМ из настроек

        // Название
        if ($form->from_legal_entity == '&nbsp;') {
            $form->from_legal_entity = $netshop->get_setting('CompanyName');
        }

        // Адрес (улица)
        if ($form->from_street == '&nbsp;') {
            $form->from_street = $netshop->get_setting('Address');
        }

        // Город
        if ($form->from_city == '&nbsp;') {
            $form->from_city = $db->get_var("SELECT Region_Name FROM Classificator_Region WHERE Region_ID = ".$netshop->get_setting('City'));
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

        // Адрес (улица)
        if ($form->to_street == '&nbsp;') {
            $form->to_street = $order->Address;
        }

        // Телефон
        if ($order->Phone != '&nbsp;') {
            $form->to_phone = $order->Phone;
        }

        // Город получателя
        if ($form->to_city == '&nbsp;') {
            $form->to_city = $db->get_var("SELECT Region_Name FROM Classificator_Region WHERE Region_ID = ".$order->City);
        }

        // Индекс получателя
        if ($form->to_zipcode == '&nbsp;') {
            $form->to_zipcode = $order->Zip;
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
            $form->value = number_format($value, 0, '', '');
            $form_value_text = nc_netshop_amount_in_full($form->value, true, false);
            if (!$nc_core->NC_UNICODE) {
                $form->value = $nc_core->utf8->utf2win($form->value);
            }
        }

        // Наложенный платеж
        $cash_on_delivery = round(str_replace(',', '.',$form->cash_on_delivery), 2);
        $form->cash_on_delivery = $cash_on_delivery;
        if ($form->cash_on_delivery) {
            $int = (int)$form->cash_on_delivery;

            $decimals = round($form->cash_on_delivery - $int, 2) * 100;
            $decimals = sprintf('%02d', $decimals);

            $form_cash_on_delivery_text = nc_netshop_amount_in_full($form->cash_on_delivery, true, false) .' '.
                nc_netshop_amount_in_full($decimals, false, false) .' '.
                nc_netshop_word_form($decimals, 'копейка', 'копейки', 'копеек');

            if (!$nc_core->NC_UNICODE) {
                $form_cash_on_delivery_text = $nc_core->utf8->utf2win($form_cash_on_delivery_text);
            }
            $form->cash_on_delivery = round($form->cash_on_delivery);
        } else {
            $form->cash_on_delivery = $form->value;
            $form_cash_on_delivery_text = $form_value_text;
            $decimals = '00';
        }

    } elseif (isset($_GET['phase']) && $_GET['phase'] == 'settings') {
        echo "<style>#wrapper .value-text, #wrapper .cash-on-delivery-kop, #wrapper .cash-on-delivery-text {display: none !important;}</style>";
    }

?><style type="text/css">
    html, body, div, img { margin: 0; padding: 0; border: 0; font-size: 100%; font: inherit; vertical-align: baseline; }
    body { line-height: 1; font: italic bold 18px "Times New Roman"; }

    #wrapper { position: relative; }
    #wrapper .field { font: italic bold 18px/18px Arial; position: absolute; overflow: visible; height: 18px; color: #000; }

    #wrapper .from-legal-entity { left: 40px; top: 117px; width: 450px; }
    #wrapper .from-fullname { left: 40px; top: 139px; width: 450px; }
    #wrapper .from-phone { left: 220px; top: 252px; width: 270px; }
    #wrapper .from-street { left: 90px; top: 158px; width: 400px; }
    #wrapper .from-house { left: 33px; top: 182px; width: 45px; text-align: center; }
    #wrapper .from-block { left: 110px; top: 182px; width: 60px; text-align: center; }
    #wrapper .from-floor { left: 185px; top: 182px; width: 50px; text-align: center; }
    #wrapper .from-apartment { left: 290px; top: 182px; width: 60px; text-align: center; }
    #wrapper .from-intercom { left: 415px; top: 182px; width: 70px; text-align: center; }
    #wrapper .from-city { left: 40px; top: 205px; width: 450px; }
    #wrapper .from-region { left: 40px; top: 229px; width: 450px; }
    #wrapper .from-zipcode { left: 66px; top: 251px; letter-spacing: 9px; }

    #wrapper .to-legal-entity { left: 530px; top: 115px; width: 450px; }
    #wrapper .to-fullname { left: 530px; top: 137px; width: 450px; }
    #wrapper .to-phone { left: 710px; top: 252px; width: 270px; }
    #wrapper .to-street { left: 560px; top: 157px; width: 400px; }
    #wrapper .to-house { left: 519px; top: 180px; width: 45px; text-align: center; }
    #wrapper .to-block { left: 596px; top: 180px; width: 60px; text-align: center; }
    #wrapper .to-floor { left: 671px; top: 180px; width: 50px; text-align: center; }
    #wrapper .to-apartment { left: 776px; top: 180px; width: 60px; text-align: center; }
    #wrapper .to-intercom { left: 901px; top: 180px; width: 70px; text-align: center; }
    #wrapper .to-city { left: 530px; top: 205px; width: 450px; }
    #wrapper .to-region { left: 530px; top: 229px; width: 450px; }
    #wrapper .to-zipcode { left: 554px; top: 251px; letter-spacing: 9px; }

    #wrapper .description { left: 40px; top: 305px; width: 450px; text-indent: 160px; line-height: 20px; }
    #wrapper .value { left: 639px; width: 100px; text-align: right; top: 355px; letter-spacing: 4.5px; }
    #wrapper .value-text { left: 580px; top: 370px; font-size: 12px; width: 390px; }
    #wrapper .cash-on-delivery { left: 633px; width: 100px; text-align: right; top: 386px; letter-spacing: 4.5px; }
    #wrapper .cash-on-delivery-kop { left: 757px; top: 386px; letter-spacing: 4.5px; }
    #wrapper .cash-on-delivery-text { left: 580px; top: 400px; font-size: 12px; width: 390px; }

    #wrapper input { width: 330px; height: 20px; line-height: 20px; margin: 0; padding: 0 5px; outline: none; }
    #wrapper input[name='from_phone'],
    #wrapper input[name='to_phone'] { width: 170px; }
    #wrapper input[name='description'] { width: 240px; }
    #wrapper input[name='value'],
    #wrapper input[name='cash_on_delivery'] { width: 200px; }
    #wrapper input[name='from_house'],
    #wrapper input[name='from_block'],
    #wrapper input[name='from_floor'],
    #wrapper input[name='from_apartment'],
    #wrapper input[name='from_intercom'],
    #wrapper input[name='to_house'],
    #wrapper input[name='to_block'],
    #wrapper input[name='to_floor'],
    #wrapper input[name='to_apartment'],
    #wrapper input[name='to_intercom'] { width: 40px; text-align: center; }
    #wrapper input[name='from_intercom'],
    #wrapper input[name='to_intercom'] { width: 50px; }
    #wrapper input[name='from_zipcode'],
    #wrapper input[name='to_zipcode'] { width: 100px; }

</style>

<div id="wrapper">
    <img src="<?php echo $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>modules/netshop/forms/templates/ems_2.png" width="1050" height="743" alt="EMS бланк для внутрироссийских отправлений"/>
    <div class="field from-legal-entity"><?= $form->from_legal_entity ?></div>
    <div class="field from-fullname"><?= $form->from_fullname ?></div>
    <div class="field from-phone"><?= $form->from_phone; ?></div>
    <div class="field from-street"><?= $form->from_street; ?></div>
    <div class="field from-house"><?= $form->from_house; ?></div>
    <div class="field from-block"><?= $form->from_block; ?></div>
    <div class="field from-floor"><?= $form->from_floor; ?></div>
    <div class="field from-apartment"><?= $form->from_apartment; ?></div>
    <div class="field from-intercom"><?= $form->from_intercom; ?></div>
    <div class="field from-city"><?= $form->from_city; ?></div>
    <div class="field from-region"><?= $form->from_region; ?></div>
    <div class="field from-zipcode"><?= $form->from_zipcode; ?></div>
    <div class="field to-legal-entity"><?= $form->to_legal_entity; ?></div>
    <div class="field to-fullname"><?= $form->to_fullname; ?></div>
    <div class="field to-phone"><?= $form->to_phone; ?></div>
    <div class="field to-street"><?= $form->to_street; ?></div>
    <div class="field to-house"><?= $form->to_house; ?></div>
    <div class="field to-block"><?= $form->to_block; ?></div>
    <div class="field to-floor"><?= $form->to_floor; ?></div>
    <div class="field to-apartment"><?= $form->to_apartment; ?></div>
    <div class="field to-intercom"><?= $form->to_intercom; ?></div>
    <div class="field to-city"><?= $form->to_city; ?></div>
    <div class="field to-region"><?= $form->to_region; ?></div>
    <div class="field to-zipcode"><?= $form->to_zipcode; ?></div>
    <div class="field description"><?= $form->description; ?></div>
    <div class="field value"><?= $form->value; ?></div>
    <div class="field value-text"><?= $form_value_text ?></div>
    <div class="field cash-on-delivery"><?= $form->cash_on_delivery; ?></div>
    <div class="field cash-on-delivery-kop"><?= $decimals; ?></div>
    <div class="field cash-on-delivery-text"><?= $form_cash_on_delivery_text; ?></div>
</div>