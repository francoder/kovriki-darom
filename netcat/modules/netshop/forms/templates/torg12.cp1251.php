<?

    if (isset($_GET['phase']) && $_GET['phase'] == 'print') {

        // ������ �� �� ��������

        // ��������
        if ($form->company_name == '&nbsp;') {
            $form->company_name = $netshop->get_setting('CompanyName');
        }


        // ����� ���������
        if ($form->template == '&nbsp;') {
            $form->template = $order->Message_ID;
        } else {
            $date_day   = substr($order->Created, 9,2);
            $date_month = substr($order->Created, 6,2);
            $date_year  = substr($order->Created, 0,4);

            // ������ ��������������� � �������
            $form->template = str_replace(
                array('%NUMBER%', '%DAY%', '%MONTH%', '%YEAR%'),
                array($order->Message_ID, $date_day, $date_month, $date_year),
                $form->template);
        }
    }

?><style type='text/css'>
    #popup {position: relative;}
    #popup .popup { display: none; position: absolute; top: 40px; width: 330px; left: 70px; font-size: 16px; line-height: 20px; padding: 20px; border: 1px solid rgba(255,0,0,.4); -webkit-border-radius: 10px; border-radius: 10px; -webkit-box-shadow: 0 0 20px rgba(0,0,0,.3); box-shadow: 0 0 20px rgba(0,0,0,.3); background: #FFF; }
    #popup:hover .popup {display: block;}
    <?= isset($_GET['phase']) && $_GET['phase'] == 'print' ? '#popup .popup {display: none !important;}' : ''?>

    .nc-netshop-form-w {font-size: 13px}
    .nc-netshop-form td input {margin:-5px 0 !important; width:100%; display: block !important; background: #FFFCF9}
    .nc-netshop-form {border-collapse: collapse; width: 100%; margin:20px 0}
    .nc-netshop-form td, .nc-netshop-form th {border:1px solid #000; padding:5px 15px; height:auto; vertical-align:middle;}
    .nc-netshop-form.compact td {padding:5px}
    .nc-netshop-form.compact td input {margin:-5px !important}
    .nc-netshop-form th {font-weight: bold; padding: 10px 15px}
    .nc-netshop-form.pbi tr {page-break-inside: avoid;}
    .nc-netshop-form .normal th {font-weight: 400; padding: 5px;}
    .nc-netshop-form td.cap {padding: 15px 15px 0px;}
    .nc-netshop-form td.cap input {margin:0 -10px 0 0 !important}
    .nc-netshop-form.compact td.cap {padding:5px 5px 0}
    .nc-netshop-form td.b0 {border-width:0}
    .nc-netshop-form td.b1 {border-width:1px}
    .nc-netshop-form td.b3 {border-width:3px}
    .nc-netshop-form td.bt {border-top-width:1px}
    .nc-netshop-form td.bb {border-bottom-width:1px}
    .nc-netshop-form td.br {border-right-width:1px}
    .nc-netshop-form td.bl {border-left-width:1px}
    .nc-netshop-form td.b3t {border-top-width:3px}
    .nc-netshop-form td.b3b {border-bottom-width:3px}
    .nc-netshop-form td.b3r {border-right-width:3px}
    .nc-netshop-form td.b3l {border-left-width:3px}
    .tar {text-align:right}
    .tac {text-align:center}
    .tal {text-align:left}
    .tsmall {font-size: .8em; line-height:1.2em !important;}
    div.cap {font-size: .8em; line-height: 1em; height:1em; overflow:hidden; margin:2px 0 -12px; text-align: center; border-top:1px solid #000;}
    .clear {clear: both;}
</style>

<div class='nc-netshop-form-w'>
<div style='float:right;margin-bottom:-10px;' class='tsmall'>
    ��������������� ����� � ����-12. ���������� �������������� ����������� ������ �� 25.12.98 � 132
</div>
<div class='clear'></div>

<table class='nc-netshop-form'>
    <col width="10%"/>
    <col width=""/>
    <col width="10%"/>
    <col width="15%"/>
<tr>
    <td class='b0 br' colspan="3"></td>
    <td>���</td>
</tr>
<tr>
    <td class='b0 br tar' colspan="3">����� �� ����</td>
    <td class='b3 bb'>0330212</td>
</tr>
<tr>
    <td class='cap b0' colspan="2">
        <?=$form->company_name ?>
        <div class='cap'>(�����������, ����������������, �����, ����� ��������, �����, ���������� ���������)</div>
    </td>
    <td class='b0 tar'>�� ����</td>
    <td class='b3l b3r'><?=$form->okpo1 ?></td>
</tr>
<tr>
    <td class='cap b0' colspan="3">
        <?=$form->unit ?>
        <div class="cap">(����������� �������������)</div>
    </td>
    <td class='b3l b3r'></td>
</tr>
<tr>
    <td colspan="3" class='b0 tar'>��� ������������ �� ����</td>
    <td class='b3l b3r'><?=$form->okdp ?></td>
</tr>
<tr>
    <td class='cap b0'>���������������</td>
    <td class='cap b0'>
        <?=$form->consignee ?>
        <div class="cap">(�����������, �����, �������, ����, ���������� ���������)</div>
    </td>
    <td class='b0 tar'>�� ����</td>
    <td class='b3l b3r'><?=$form->okpo2 ?></td>
</tr>
<tr>
    <td class='cap b0'>���������</td>
    <td class='cap b0'>
        <?=$form->supplier ?>
        <div class="cap">(�����������, �����, �������, ����, ���������� ���������)</div>
    </td>
    <td class='b0 tar'>�� ����</td>
    <td class='b3l b3r'><?=$form->okpo3 ?></td>
</tr>
<tr>
    <td class='cap b0'>����������</td>
    <td class='cap b0'>
        <?=$form->payer ?>
        <div class="cap">(�����������, �����, �������, ����, ���������� ���������)</div>
    </td>
    <td class='b0 tar'>�� ����</td>
    <td class='b3l b3r'><?=$form->okpo4 ?></td>
</tr>
<tr>
    <td class='cap b0'>���������</td>
    <td class='cap b0'>
        <?=$form->contract ?>
        <div class="cap">(�������, �����-�����)</div>
    </td>
    <td class='b1 tar'>�����</td>
    <td class='b3l b3r'><?=$form->trans_number1 ?></td>
</tr>
<tr>
    <td colspan="2" class='b0'></td>
    <td class='b1 tar'>����</td>
    <td class='b3l b3r'><?=$form->trans_date1 ?></td>
</tr>
<tr>
    <td colspan="2" class='b0 tar'>������������ ���������</td>
    <td class='b1 tar'>�����</td>
    <td class='b3l b3r'><?=$form->trans_number2 ?></td>
</tr>
<tr>
    <td colspan="2" class='b0'></td>
    <td class='b1 tar'>����</td>
    <td class='b3l b3r'><?=$form->trans_date2 ?></td>
</tr>
<tr>
    <td colspan="2" class='b0'></td>
    <td class='b0 tar'>��� ��������</td>
    <td class='b3l b3r b3b'><?=$form->operation_type ?></td>
</tr>

</table>

<table class='nc-netshop-form' style='width:auto; margin:-80px 0 0 10%'>
<tr>
    <td rowspan="2" class='b0 tac'><h3>�������� ���������</h3></td>
    <td>����� ���������</td>
    <td>���� �����������</td>
</tr>
<tr>
    <td>
        <div id="popup">
            <b><?= $form->template ?></b>
            <div class="popup">
                � ������� ��������� ���������������: <br><br>
                %NUMBER% - ����� ������ <br>
                %DAY% - ���� ������ <br>
                %MONTH% - ����� ������ <br>
                %YEAR% - ��� ������ <br><br>
                ������: ����12_%NUMBER%_%YEAR%,<br>
                �������: ����12_1_2015
            </div>
        </div>
    </td>
    <td><b><?= $current_date ?></b></td>
</tr>
</table>

<? $defaultVatRate = ((int)$form->nds ? $form->nds : $shop->get_setting('VAT')); ?>

<table class='nc-netshop-form compact pbi'>
    <col width=1 /><col /><col width=10% /><col width=10% /><col width=10% />

    <thead style="display: table-header-group" class="normal">
        <tr class="tac">
            <th rowspan="2">�</th>
            <th colspan="2">�����</th>
            <th colspan="2">������� ���������</th>
            <th rowspan="2">��� ��������</th>
            <th colspan="2">����������</th>
            <th rowspan="2">����� ������</th>
            <th rowspan="2">���-�� (����� �����)</th>
            <th rowspan="2">����, ���. ���.</th>
            <th rowspan="2">����� ��� ����� ���, ���. ���.</th>
            <th colspan="2">���</th>
            <th rowspan="2">����� � ������ ���, ���. ���.</th>
        </tr>
        <tr class="tac">
            <th>������������, ��������������, ����, ������� ������</th>
            <th>���</th>
            <th>�����&shy;�������</th>
            <th>��� �� ����</th>
            <th>� ����� �����</th>
            <th>����, ����</th>
            <th class='tac'>������, %</th>
            <th>�����, ���. ���.</th>
        </tr>
        <tr>
            <? for($i=1; $i<16; $i++): ?><th class='tac'><?=$i ?></th><? endfor ?>
        </tr>
    </thead>

    <tbody>
        <? $totalPrice = $totalQty = $totalVat = $totalPriceNoVat = 0 ?>
        <? foreach ($order_items as $i => $product): ?>
        <? $itemVatRate = $product['VAT'] ? $product['VAT'] : $defaultVatRate; ?>
        <? $itemVat = $product['TotalPrice'] * $itemVatRate/100 ?>
        <tr>
            <td class='tac'><i><?=$i+1 ?></i></td>
            <td><?=$product['FullName'] ?></td>
            <td><?=$product['ItemID'] ?></td>
            <td><?=$product['Units'] ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class='tac'><?=$product['Qty'] ?></td>
            <td class='tar'><?=number_format($product['ItemPrice'], 2, '-', '') ?></td>
            <td class='tar'><?=number_format($product['TotalPrice'] - $itemVat, 2, '-', '') ?></td>
            <td class='tac'><?=$itemVatRate ?></td>
            <td class='tar'><?=number_format($itemVat, 2, '-', '') ?></td>
            <td class='tar'><?=number_format($product['TotalPrice'], 2, '-', '') ?></td>
        </tr>
            <? $totalPrice      += $product['TotalPrice'] ?>
            <? $totalVat        += $itemVat ?>
            <? $totalPriceNoVat += $product['TotalPrice'] - $itemVat ?>
            <? $totalQty        += $product['Qty'] ?>
        <? endforeach ?>
    </tbody>

    <tfoot>
        <tr style='font-weight:bold'>
            <td colspan='7' class='b0 br tar'><?=NETCAT_MODULE_NETSHOP_BANK_TOTAL ?>:</td>
            <td></td>
            <td></td>
            <td class='tac'><?=$totalQty ?></td>
            <td class='tac'>X</td>
            <td class='tar'><?=number_format($totalPriceNoVat, 2, '-', '') ?></td>
            <td class='tac'>X</td>
            <td class='tar'><?=number_format($totalVat, 2, '-', '') ?></td>
            <td class='tar'><?=number_format($totalPrice, 2, '-', '') ?></td>
        </tr>
        <tr style='font-weight:bold'>
            <td colspan='7' class='b0 br tar'>����� �� ���������:</td>
            <td></td>
            <td></td>
            <td class='tac'><?=$totalQty ?></td>
            <td class='tac'>X</td>
            <td class='tar'><?=number_format($totalPriceNoVat, 2, '-', '') ?></td>
            <td class='tac'>X</td>
            <td class='tar'><?=number_format($totalVat, 2, '-', '') ?></td>
            <td class='tar'><?=number_format($totalPrice, 2, '-', '') ?></td>
        </tr>
    </tfoot>
</table>

<table class='nc-netshop-form' style="width:80%;">
<tr>
    <td class='b0 tar cap' width="30%" nowrap="nowrap">�������� ��������� ����� ���������� ��</td>
    <td class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
    <td class='b0 cap' width="200">������</td>
</tr>
<tr>
    <td class='b0 tar cap' width="30%" nowrap="nowrap">� ��������</td>
    <td class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
    <td class='b0 cap'>���������� ������� �������</td>
</tr>
</table>


<table class='nc-netshop-form'>
<tr>
    <td class='b0' width="80" colspan="2"></td>
    <td class='b0 tar cap' width="120" nowrap="nowrap">����� ����� (�����)</td>
    <td class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
    <td class='b3 cap' width="200"></td>
</tr>
<tr>
    <td class='b0 tar cap' width="80" nowrap="nowrap">����� ����</td>
    <td class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
    <td class='b0 tar cap' width="120" nowrap="nowrap">����� ����� (������)</td>
    <td class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
    <td class='b3 cap' width="200"></td>
</tr>
</table>


<table class='nc-netshop-form'>
<tr>
<td class='b0 br' style='width:50%;padding:0;vertical-align: top;'>

    <table class='nc-netshop-form' style='margin:0'>
    <col width="100" />
    <col width="" />
    <col width="1" />
    <tr>
        <td class='b0 tar cap' nowrap="nowrap">���������� ��</td>
        <td class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
        <td class='b0 cap'>������</td>
    </tr>
    <tr>
        <td class='b0 tar cap' width="30%" nowrap="nowrap">����� �������� �� �����</td>
        <td colspan="2" class='b0 cap'>&nbsp;<div class="cap">(��������)</div></td>
    </tr>
    </table>

    <table class='nc-netshop-form' style='margin:0'>
    <col width="70%" />
    <col width="1" />
    <col width="" />
    <col width="1" />
    <tr>
        <td class='b0 cap'>&nbsp;<div class="cap">&nbsp;</div></td>
        <td class='b0 cap tac'>���.</td>
        <td class='b0 cap'>&nbsp;<div class="cap">&nbsp;</div></td>
        <td class='b0 cap tac'>���.</td>
    </tr>
    </table>

    <table class='nc-netshop-form' style='margin:0'>
    <col width="30%" />
    <col width="20%" />
    <col width="25%" />
    <col width="25%" />
    <tr>
        <td class='b0 cap'>������ ����� ��������</td>
        <td class='b0 cap'><?=$form->resolved_by_position ?><div class="cap">(���������)</div></td>
        <td class='b0 cap'>&nbsp;<div class="cap">(�������)</div></td>
        <td class='b0 cap'><?=$form->resolved_by_surname ?><div class="cap">(����������� �������)</div></td>
    </tr>
    <tr>
        <td class='b0 cap' colspan="2">������� ���������</td>
        <td class='b0 cap'>&nbsp;<div class="cap">(�������)</div></td>
        <td class='b0 cap'><?=$form->accountant_surname ?><div class="cap">(����������� �������)</div></td>
    </tr>
    <tr>
        <td class='b0 cap'>������ ����� ��������</td>
        <td class='b0 cap'><?=$form->released_by_position ?><div class="cap">(���������)</div></td>
        <td class='b0 cap'>&nbsp;<div class="cap">(�������)</div></td>
        <td class='b0 cap'><?=$form->released_by_surname ?><div class="cap">(����������� �������)</div></td>
    </tr>
    </table>
</td>
<td class='b0 bl' style='width:50%;padding:0;vertical-align: top;'>

    <table class='nc-netshop-form' style='margin:0'>
    <col width="150" />
    <col width="" />
    <col width="100" />
    <tr>
        <td class='b0 cap'>�� ������������ �</td>
        <td class='b0 cap'>&nbsp;<div class="cap"></div></td>
        <td class='b0 cap'>��&nbsp;�_____�&nbsp;&nbsp;______________&nbsp;&nbsp;________&nbsp;����</td>
    </tr>
    <tr>
        <td class='b0 cap'>��������</td>
        <td colspan="2" class='b0 cap'>&nbsp;<div class="cap">(���, ���� (�����������, ���������, �������, �., �.))</div></td>
    </tr>
    <tr>
        <td colspan="3" class='b0 cap'>&nbsp;<div class="cap"></div></td>
    </tr>
    <tr>
        <td colspan="3" class='b0 cap'>&nbsp;<div class="cap"></div></td>
    </tr>
    </table>


    <table class='nc-netshop-form' style='margin:0'>
    <col width="30%" />
    <col width="20%" />
    <col width="25%" />
    <col width="25%" />
    <tr>
        <td class='b0 cap'>���� ������</td>
        <td class='b0 cap'>&nbsp;<div class="cap">(���������)</div></td>
        <td class='b0 cap'>&nbsp;<div class="cap">(�������)</div></td>
        <td class='b0 cap'>&nbsp;<div class="cap">(����������� �������)</div></td>
    </tr>
    <tr>
        <td class='b0 cap'>���� ������� ���������������</td>
        <td class='b0 cap'>&nbsp;<div class="cap">(���������)</div></td>
        <td class='b0 cap'>&nbsp;<div class="cap">(�������)</div></td>
        <td class='b0 cap'>&nbsp;<div class="cap">(����������� �������)</div></td>
    </tr>
    </table>
</td>
</tr>
<tr>
    <td class='b0 br' style='width:50%;padding:0;vertical-align: top;'>
    <table class='nc-netshop-form' style='margin:0'>
    <tr>
        <td class='b0 cap'>�.�.</td>
        <td class='b0 cap'>&nbsp;�_____�&nbsp;&nbsp;______________&nbsp;&nbsp;________&nbsp;����</td>
    </tr>
    </table>
    </td>
    <td class='b0 bl' style='width:50%;padding:0;vertical-align: top;'>
    <table class='nc-netshop-form' style='margin:0'>
    <tr>
        <td class='b0 cap'>�.�.</td>
        <td class='b0 cap'>&nbsp;�_____�&nbsp;&nbsp;______________&nbsp;&nbsp;________&nbsp;����</td>
    </tr>
    </table>
    </td>
</tr>
</table>


</div>