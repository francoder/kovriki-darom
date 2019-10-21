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

        // ИНН
        if ($form->from_inn == '&nbsp;') {
            $form->from_inn = $netshop->get_setting('INN');
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


        // Наложенный платеж
        $cash_on_delivery = round(str_replace(',', '.',$form->cash_on_delivery), 2);
        $form->cash_on_delivery = $cash_on_delivery;

        if ($form->cash_on_delivery) {
            $int = (int)$form->cash_on_delivery;

            $decimals = round($form->cash_on_delivery - $int, 2) * 100;
            $decimals = sprintf('%02d', $decimals);

            $form_cash_on_delivery_text = nc_netshop_amount_in_full($form->cash_on_delivery, false, false) .' руб ' . $decimals . ' коп';

            if (!$nc_core->NC_UNICODE) {
                $form_cash_on_delivery_text = $nc_core->utf8->utf2win($form_cash_on_delivery_text);
            }
            $form->cash_on_delivery = round($form->cash_on_delivery);

        } else {

            $value = 0;
            foreach ($order_items as $product) {
                $value += $product['TotalPrice'];
            }

            if ($value) {
                $form->cash_on_delivery = round($value);
                $int = (int)$form->cash_on_delivery;
                $decimals = round($form->cash_on_delivery - $int, 2) * 100;
                $decimals = sprintf('%02d', $decimals);
                $form_cash_on_delivery_text = nc_netshop_amount_in_full($form->cash_on_delivery, false, false) .' руб ' . $decimals . ' коп';
            } else  {
                $decimals = '00';
                $form_cash_on_delivery_text = '';
            }
        }
    }

?><style>
    html, body, div, img { margin: 0; padding: 0; border: 0; font-size: 100%; font: inherit; vertical-align: baseline; }
    body { line-height: 1; }

    #wrapper { position: relative; }
    #wrapper .field  { font: italic normal 18px/18px Arial; position: absolute; overflow: visible; height: 18px; color: #000; }

    #wrapper .form1 { position: absolute; top: 0; left: 0; }
    #wrapper .form2 { position: absolute; top: 1414px; left: 0; }

    #wrapper .cash-on-delivery { left: 740px; top: 346px; width: 64px; text-align: center; }
    #wrapper .cash-on-delivery-dec { left: 830px; top: 346px; width: 27px; text-align: center; }
    #wrapper .cash-on-delivery-text { left: 400px; top: 370px; height: 20px; width: 490px; font-size: 14px; text-align: center; }

    #wrapper .from-fullname { left: 460px; top: 711px; width: 430px; }
    #wrapper .from-address-line1 { left: 540px; top: 753px; width: 350px; }
    #wrapper .from-address-line2 { left: 400px; top: 774px; width: 490px; }
    #wrapper .from-zipcode { left: 814px; top: 796px; font-size: 17px; letter-spacing: 3px; }
    #wrapper .from-inn1 { left: 742px; top: 711px; width: 146px; font-size: 16px; letter-spacing: 2.6px; font-family: monospace; font-style: normal; }

    #wrapper .from-inn { left: 436px; top: 526px; font-size: 16px; letter-spacing: 3px; font-family: monospace; font-style: normal; }
    #wrapper .from-corr { left: 643px; top: 525px; font-size: 17px; letter-spacing: 2px; font-family: monospace; font-style: normal; }
    #wrapper .from-account { left: 467px; top: 567px; font-size: 17px; letter-spacing: 2px; font-family: monospace; font-style: normal; }
    #wrapper .from-bik { left: 777px; top: 567px; font-size: 17px; letter-spacing: 2px; font-family: monospace; font-style: normal; }
    #wrapper .from-bank { left: 540px; top: 547px; width: 345px; font-size: 16px; }

    #wrapper .to-fullname { left: 437px; top: 401px; width: 450px; }
    #wrapper .to-address-line1 { left: 435px; top: 445px; width: 450px; }
    #wrapper .to-address-line2 { left: 400px; top: 466px; width: 490px; }
    #wrapper .to-zipcode { left: 814px; top: 487px; font-size: 17px; letter-spacing: 3px; }

    #wrapper input { width: 330px; height: 20px; line-height: 20px; margin: 0; padding: 0 5px; outline: none; }

    #wrapper input[name='value'],
    #wrapper input[name='cash_on_delivery'] { width: 150px; text-align: center;}

    #wrapper input[name='from_inn'],
    #wrapper input[name='receiver_bik'],
    #wrapper input[name='receiver_inn'],
    #wrapper input[name='receiver_corr'],
    #wrapper input[name='receiver_account'] { width: 246px; position: absolute; top: -1px; left: -1px; letter-spacing: 3px; text-align: center; }

    #wrapper input[name='receiver_inn'] { width: 125px; top: -2px; }
    #wrapper input[name='receiver_bik'] { width: 111px; top: -1px; }

    #wrapper input[name='from_inn'] { width: 146px; }
    #wrapper input[name='from_fullname'] { width: 235px; }
    #wrapper input[name='from_zipcode'],
    #wrapper input[name='to_zipcode'] { width: 75px; padding: 0 3px; margin-left: -2px; position: absolute; top: -3px; font-size: 15px; letter-spacing: 3px; }

</style>

<div id="wrapper">
    <img class="form1" src="<?php echo $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>modules/netshop/forms/templates/russianpost_f113_page1.png" width="1000" height="1414" alt="Бланк почтового перевода наложенного платежа ф113эн" />
    <img class="form2" src="<?php echo $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH ?>modules/netshop/forms/templates/russianpost_f113_page2.png" width="1000" height="1414" alt="Бланк почтового перевода наложенного платежа ф113эн оборотная сторона" />

    <div class="field cash-on-delivery"><?= $form->cash_on_delivery; ?></div>

    <div class="field from-fullname"><?= $form->from_fullname; ?></div>
    <div class="field from-address-line1"><?= $form->from_address_line1; ?></div>
    <div class="field from-address-line2"><?= $form->from_address_line2; ?></div>
    <div class="field from-zipcode"><?= $form->from_zipcode; ?></div>
    <div class="field from-inn1"><?= $form->from_inn; ?></div>

    <div class="field from-inn"><?= $form->receiver_inn; ?></div>
    <div class="field from-corr"><?= $form->receiver_corr; ?></div>
    <div class="field from-account"><?= $form->receiver_account; ?></div>
    <div class="field from-bank"><?= $form->receiver_bank; ?></div>
    <div class="field from-bik"><?= $form->receiver_bik; ?></div>

    <div class="field to-fullname"><?= $form->to_fullname; ?></div>
    <div class="field to-address-line1"><?= $form->to_address_line1; ?></div>
    <div class="field to-address-line2"><?= $form->to_address_line2; ?></div>
    <div class="field to-zipcode"><?= $form->to_zipcode; ?></div>

    <? if (isset($_GET['phase']) && $_GET['phase'] == 'print') { ?>
    <div class="field cash-on-delivery-dec"><?= $decimals; ?></div>
    <div class="field cash-on-delivery-text"><?= $form_cash_on_delivery_text ?></div>
    <? } ?>
</div>