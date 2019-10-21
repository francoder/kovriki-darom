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
            $form->to_address_line2 = 'г.' . $db->get_var("SELECT Region_Name FROM Classificator_Region WHERE Region_ID = ".$order->City);
        }

        // Индекс получателя
        if ($order->Zip != '&nbsp;') {
            $form->to_zipcode = $order->Zip;
        }


        // Стоимость
        $valuation = round(str_replace(',', '.',$form->value), 2);
        $form->value = $valuation;
        if ($form->value) {
            $int = (int)$form->value;

            $decimals = round($form->value - $int, 2) * 100;
            $decimals = sprintf('%02d', $decimals);

            $form_value_text = $int . ' (' .
                nc_netshop_amount_in_full($form->value, false, false) .
                ') руб. ' . $decimals . ' коп.';
            if (!$nc_core->NC_UNICODE) {
                $form_value_text = $nc_core->utf8->utf2win($form_value_text);
            }
            $form->value = round($form->value);
        } else {
            $value = 0;
            foreach ($order_items as $product) {
                $value += $product['TotalPrice'];
            }

            if ($value) {
                $form->value = round($value);
                $int = (int)$form->value;
                $decimals = round($form->value - $int, 2) * 100;
                $decimals = sprintf('%02d', $decimals);
                $form_value_text = $int . ' (' .
                    nc_netshop_amount_in_full($form->value, false, false) .
                    ') руб. ' . $decimals . ' коп.';
            } else  {
                $form_value_text = '';
            }

        }

        // Наложенный платеж
        $cash_on_delivery = round(str_replace(',', '.',$form->cash_on_delivery), 2);
        $form->cash_on_delivery = $cash_on_delivery;
        if ($form->cash_on_delivery) {
            $int = (int)$form->cash_on_delivery;

            $decimals = round($form->cash_on_delivery - $int, 2) * 100;
            $decimals = sprintf('%02d', $decimals);

            $form_cash_on_delivery_text = $int . ' (' .
                nc_netshop_amount_in_full($form->cash_on_delivery, false, false) .
                ') руб. ' . $decimals . ' коп.';

            if (!$nc_core->NC_UNICODE) {
                $form_cash_on_delivery_text = $nc_core->utf8->utf2win($form_cash_on_delivery_text);
            }
            $form->cash_on_delivery = round($form->cash_on_delivery);
        } else {
            $form->cash_on_delivery = $form->value;
            $form_cash_on_delivery_text = $form_value_text;
        }


        // Вес
        $form->weight = round(str_replace(',', '.', $form->weight), 3);
        if ($form->weight) {
            $int = (int)$form->weight;

            $decimals = round($form->weight - $int, 3) * 1000;

            $form->weight = $int . ' кг ' . ( $decimals ? $decimals . ' гр' : '');
            if (!$nc_core->NC_UNICODE) {
                $form->weight = $nc_core->utf8->utf2win($form->weight);
            }
        }
    }

?><style>
    html, body, div, img { margin: 0; padding: 0; border: 0; font-size: 100%; font: inherit; vertical-align: baseline; }
    body { line-height: 1; }

    #wrapper { position: relative; }
    #wrapper .field { font: italic normal 18px/18px Arial; position: absolute; overflow: visible; height: 18px; color: #000; }

    #wrapper .form1 { position: absolute; top: 0; left: 0; }
    #wrapper .form2 { position: absolute; top: 1414px; left: 0; }

    #wrapper .from-fullname { left: 255px; top: 615px; width: 540px; }
    #wrapper .from-address-line1 { left: 245px; top: 649px; width: 550px; }
    #wrapper .from-address-line2 { left: 200px; top: 676px; width: 450px; }
    #wrapper .from-zipcode { left: 658px; top: 673px; font-size: 25px; letter-spacing: 10px; }

    #wrapper .to-fullname { left: 240px; top: 494px; width: 380px; }
    #wrapper .to-address-line1 { left: 245px; top: 518px; width: 380px; }
    #wrapper .to-address-line2 { left: 200px; top: 549px; width: 420px; }
    #wrapper .to-zipcode { left: 483px; top: 577px; font-size: 25px; letter-spacing: 10px; }

    #wrapper .bottom-to-fullname { left: 238px; top: 1028px; width: 560px; }
    #wrapper .bottom-to-address-line1 { left: 245px; top: 1065px; width: 552px; }
    #wrapper .bottom-to-address-line2 { left: 200px; top: 1103px; width: 450px; }
    #wrapper .bottom-to-zipcode { left: 657px; top: 1097px; font-size: 25px; letter-spacing: 10px; }

    #wrapper .value { left: 260px; top: 991px; width: 180px; text-align: center; }
    #wrapper .value-text { left: 200px; top: 422px;  width: 420px; font-size: 14px; text-align: center; }
    #wrapper .cash-on-delivery { left: 580px; top: 990px; width: 170px; text-align: center; }
    #wrapper .cash-on-delivery-text { left: 200px; top: 462px; width: 420px; font-size: 14px; text-align: center; }
    #wrapper .weight1 { left: 685px; top: 440px; width: 130px; text-align: center; }
    #wrapper .weight2 { left: 285px; top: 910px; width: 160px; text-align: center; }

    #wrapper input { width: 330px; height: 20px; line-height: 20px; margin: 0; padding: 0 5px; outline: none; }

    #wrapper input[name='value'],
    #wrapper input[name='cash_on_delivery'] { width: 150px; text-align: center;}

    #wrapper input[name='weight'] { width: 100px; }
    #wrapper input[name='from_zipcode'],
    #wrapper input[name='to_zipcode'] { width: 145px; height: 30px; line-height: 30px; margin-left: -4px; position: absolute; top: -6px; font-size: 25px; letter-spacing: 9px; }
</style>

<div id="wrapper">
    <img class="form1" src="<?php echo $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>modules/netshop/forms/templates/russianpost_f116_page1.png" width="1000" height="1414" alt="Бланк сопроводительного адреса к посылке ф116" />
    <img class="form2" src="<?php echo $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>modules/netshop/forms/templates/russianpost_f116_page2.png" width="1000" height="1414" alt="Бланк сопроводительного адреса к посылке ф116 оборотная сторона" />

    <div class="field from-fullname"><?= $form->from_fullname; ?></div>
    <div class="field from-address-line1"><?= $form->from_address_line1; ?></div>
    <div class="field from-address-line2"><?= $form->from_address_line2; ?></div>
    <div class="field from-zipcode"><?= $form->from_zipcode; ?></div>

    <div class="field to-fullname"><?= $form->to_fullname; ?></div>
    <div class="field to-address-line1"><?= $form->to_address_line1; ?></div>
    <div class="field to-address-line2"><?= $form->to_address_line2; ?></div>
    <div class="field to-zipcode"><?= $form->to_zipcode; ?></div>

    <div class="field value"><?= $form->value; ?></div>
    <div class="field cash-on-delivery"><?= $form->cash_on_delivery; ?></div>

    <div class="field weight1"><?= $form->weight; ?></div>

    <? if (isset($_GET['phase']) && $_GET['phase'] == 'print') { ?>
    <div class="field weight2"><?= $form->weight; ?></div>
    <div class="field value-text"><?= $form_value_text; ?></div>
    <div class="field cash-on-delivery-text"><?= $form_cash_on_delivery_text; ?></div>

    <div class="field bottom-to-fullname"><?= $form->to_fullname; ?></div>
    <div class="field bottom-to-address-line1"><?= $form->to_address_line1; ?></div>
    <div class="field bottom-to-address-line2"><?= $form->to_address_line2; ?></div>
    <div class="field bottom-to-zipcode"><?= $form->to_zipcode; ?></div>
    <? } ?>
</div>