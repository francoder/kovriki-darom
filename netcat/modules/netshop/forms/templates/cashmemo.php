<?

/** @var $order_items_with_distributed_cart_discount array|nc_netshop_item_collection */
/** @var $order nc_netshop_form_data */
/** @var $current_date string */
/** @var $netshop nc_netshop */

if (isset($_GET['phase']) && $_GET['phase'] === 'print') {

    // Данные ИМ из настроек

    // Название
    if ($form->company_name === '&nbsp;') {
        $form->company_name = $netshop->get_setting('CompanyName');
    }

    // Адрес строка 1
    if ($form->company_address === '&nbsp;') {
        $form->company_address = $netshop->get_setting('Address');
    }

    // ИНН
    if ($form->company_inn === '&nbsp;') {
        $form->company_inn = $netshop->get_setting('INN');
    }

    // Номер документа
    if ($form->template === '&nbsp;') {
        $form->template = $order->Message_ID;
    } else {
        $date_day = substr($order->Created, 9, 2);
        $date_month = substr($order->Created, 6, 2);
        $date_year = substr($order->Created, 0, 4);

        // Замена макропеременных в шаблоне
        $form->template = str_replace(
            array('%NUMBER%', '%DAY%', '%MONTH%', '%YEAR%'),
            array($order->Message_ID, $date_day, $date_month, $date_year),
            $form->template
        );
    }
}

/** @var $nc_netshop_order nc_netshop_order|array|null */
$nc_netshop_order = $order->_data;

// выводится стоимость товаров с распределённой скидкой на корзину,
// поэтому, если скидка не может быть целиком распределена по товарам,
// $nc_netshop_order['TotalPriceF'] может не соответствовать
// сумме стоимости товаров в товарном чеке
$total_sum = $order_items_with_distributed_cart_discount->sum('TotalPrice');

// не используется, но может понадобиться при добавлении строки с количеством
// позиций и суммы прописью:
$total_count = $order_items_with_distributed_cart_discount->count();

?>
<style>
    #popup {
        position: relative;
        display: inline-block;
    }

    #popup .popup {
        text-align: left;
        font-weight: 400;
        display: none;
        position: absolute;
        top: 40px;
        width: 330px;
        left: 70px;
        font-size: 16px;
        line-height: 20px;
        padding: 20px;
        border: 1px solid rgba(255, 0, 0, .4);
        -webkit-border-radius: 10px;
        border-radius: 10px;
        -webkit-box-shadow: 0 0 20px rgba(0, 0, 0, .3);
        box-shadow: 0 0 20px rgba(0, 0, 0, .3);
        background: #FFF;
    }

    #popup:hover .popup {
        display: block;
    }

    <?= isset($_GET['phase']) && $_GET['phase'] === 'print' ? '#popup .popup {display: none !important;}' : ''; ?>

    .nc-netshop-form-w input {
        background: #FFFCF9
    }

    .nc-netshop-form-w td input {
        margin: -5px 0 !important;
        width: 100%;
        display: block !important;
    }

    .nc-netshop-form {
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0
    }

    .nc-netshop-form td, .nc-netshop-form th {
        border: 1px solid #000;
        padding: 5px 15px;
        height: auto;
    }

    .nc-netshop-form th {
        font-weight: bold;
        padding: 10px 15px
    }

    .nc-netshop-form-price-cell {
        text-align: right;
        white-space: nowrap;
    }
</style>

<div class="nc-netshop-form-w">
    <?= $form->company_name; ?>, <?= NETCAT_MODULE_NETSHOP_BANK_INN; ?> <?= $form->company_inn; ?><br>
    <?= $form->company_address; ?><br>
    <div style='font-weight:bold; text-align:center'>
        <?= NETCAT_MODULE_NETSHOP_CASHMEMO; ?> №
        <div id="popup">
            <b><?= $form->template ?></b>
            <div class="popup">
                В шаблоне допустимы макропеременные: <br><br>
                %NUMBER% - Номер заказа <br>
                %DAY% - День заказа <br>
                %MONTH% - Месяц заказа <br>
                %YEAR% - Год заказа <br><br>
                Пример: ИМ_%NUMBER%_%YEAR%<br>
                Получим: ИМ_1_2015
            </div>
        </div>
        от <?= $current_date; ?> г.
    </div>

    <table class='nc-netshop-form'>
        <col width="1"/>
        <col/>
        <col width="10%"/>
        <col width="10%"/>
        <col width="10%"/>
        <tr>
            <th>№</th>
            <th><?= NETCAT_MODULE_NETSHOP_ITEM; ?></th>
            <th style='text-align:center'><?= NETCAT_MODULE_NETSHOP_ITEM_PRICE; ?></th>
            <th style='text-align:center'><?= NETCAT_MODULE_NETSHOP_QTY; ?></th>
            <th style='text-align:center'><?= NETCAT_MODULE_NETSHOP_BANK_SUM; ?></th>
        </tr>
        <? foreach ($order_items_with_distributed_cart_discount as $i => $product): ?>
            <tr>
                <td><?= $i + 1; ?></td>
                <td><?= $product['FullName'] ?></td>
                <td class='nc-netshop-form-price-cell'><?= $product['ItemPriceF']; ?></td>
                <td style='text-align:center'><?= $product['Qty']; ?></td>
                <td class='nc-netshop-form-price-cell'><?= $product['TotalPriceF']; ?></td>
            </tr>
        <? endforeach; ?>
        <? if (!empty($nc_netshop_order['DeliveryCost'])): ?>
            <?php
            $total_count++;
            $total_sum += $nc_netshop_order['DeliveryCost'];
            ?>
            <tr>
                <td><?= ++$i + 1 ?></td>
                <td><?= NETCAT_MODULE_NETSHOP_CASHMEMO_DELIVERY; ?></td>
                <td class='nc-netshop-form-price-cell'><?= $nc_netshop_order['DeliveryPriceF']; ?></td>
                <td style='text-align:center'>1</td>
                <td class='nc-netshop-form-price-cell'><?= $nc_netshop_order['DeliveryPriceF']; ?></td>
            </tr>
        <? endif; ?>
        <? if (!empty($nc_netshop_order['PaymentCost'])): ?>
            <?php
            $total_count++;
            $total_sum += $nc_netshop_order['PaymentCost'];
            ?>
            <tr>
                <td><?= ++$i + 1 ?></td>
                <td><?= NETCAT_MODULE_NETSHOP_CASHMEMO_PAYMENT; ?></td>
                <td class='nc-netshop-form-price-cell'><?= $nc_netshop_order['PaymentPriceF']; ?></td>
                <td style='text-align:center'>1</td>
                <td class='nc-netshop-form-price-cell'><?= $nc_netshop_order['PaymentPriceF']; ?></td>
            </tr>
        <? endif; ?>
        <tr style='font-weight:bold'>
            <td colspan="4" style='text-align:right;'><?= NETCAT_MODULE_NETSHOP_SUM ?>:</td>
            <td class='nc-netshop-form-price-cell'><?= $netshop->format_price($total_sum) ?></td>
        </tr>
    </table>
    <br>
    <?= NETCAT_MODULE_NETSHOP_CASHMEMO_SELLER; ?>: ______________________ <?= $form->seller_position; ?> <?= $form->seller_fio; ?>
</div>