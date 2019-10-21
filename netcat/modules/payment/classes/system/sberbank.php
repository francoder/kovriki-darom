<?

class nc_payment_system_sberbank extends nc_payment_system {

    protected $automatic = FALSE;

    // принимаемые валюты
    protected $accepted_currencies = array('RUB', 'RUR');

    // параметры сайта в платежной системе
    protected $settings = array(
        'receiver' => null,
        'INN' => null,
        'bankAccount' => null,
        'bankName' => null,
        'correspondentAccount' => null,
        'KPP' => null,
        'BIK' => null,
    );

    public function execute_payment_request(nc_payment_invoice $invoice) {
        
        if (class_exists('nc_netshop')) {
        
            $netshop = nc_netshop::get_instance();
        
            // Массив соответствий имен полей:
            // "поле в настройках платежной системы" => "поле в настройках модуля магазина"
            $settings_fields_relation = array(
                'receiver' => 'ShopName',
                'INN' => 'INN',
                'bankAccount' => 'BankAccount',
                'bankName' => 'BankName',
                'correspondentAccount' => 'CorrespondentAccount',
                'KPP' => 'KPP',
                'BIK' => 'BIK'
            );
            
            // Если какие-то данные платежной системы не заполнены,
            // возьмем их из настроек модуля магазина
            foreach ($settings_fields_relation as $ps_field_name => $netshop_field_name) {
                if (!$this->get_setting($ps_field_name)) {
                    $this->settings[$ps_field_name] = $netshop->get_setting($netshop_field_name);
                }
            }
        }
        
        ?>
        <table CELLSPACING="0" BORDER="1" CELLPADDING="3" WIDTH="640"
               bordercolorlight="#000000" bordercolordark="#FFFFFF">
            <tr>
                <td ALIGN="left" WIDTH="240" VALIGN="middle">&nbsp;&nbsp;<b>ИЗВЕЩЕНИЕ</b>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    &nbsp;&nbsp;Кассир<br>
                </td>
                <td ALIGN="right" WIDTH="400" VALIGN="middle">

                    <table CELLSPACING="0" BORDER="1" CELLPADDING="3" WIDTH="410"
                           bordercolorlight="#000000" height=100% bordercolordark="#FFFFFF">
                        <tr>
                            <td colspan="3">
                                Получатель платежа: <?= $this->get_setting('receiver'); ?><br>
                                ИНН: <?= $this->get_setting('INN'); ?><br>
                                Р/c: <?= $this->get_setting('bankAccount'); ?>, <?= $this->get_setting('bankName'); ?>
                                <br>
                                Корр.сч.: <?= $this->get_setting('correspondentAccount'); ?><br>
                                КПП: <?= $this->get_setting('KPP'); ?><br>
                                БИК: <?= $this->get_setting('BIK'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td COLSPAN="3"><br>
                                <br>
                                <hr size="1" color="#000000">
                                <div align="center"
                                     style="font-family: sans-serif; font-size: xx-small">фамилия, и.о., адрес
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td ALIGN="center">Вид платежа</td>
                            <td ALIGN="center" width=15%>Дата</td>
                            <td ALIGN="center" width=15%>Сумма</td>
                        </tr>
                        <tr>
                            <td ALIGN="left"><?= $invoice->get_description(); ?></td>
                            <td valign="bottom">__________</td>
                            <td valign="bottom"><?= $invoice->get_amount(); ?></td>
                        </tr>
                        <tr>
                            <td ALIGN="left" ROWSPAN="2" colspan="3" valign="center">Плательщик:</td>
                        </tr>
                    </table>

                </td>
            </tr>
            <tr>
                <td ALIGN="left" WIDTH="240" VALIGN="middle">&nbsp;&nbsp;<b>КВИТАНЦИЯ</b>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    &nbsp;&nbsp;Кассир<br>
                </td>
                <td ALIGN="right" VALIGN="middle">

                    <table CELLSPACING="0" BORDER="1" CELLPADDING="3" WIDTH="410"
                           height=100% bordercolorlight="#000000" bordercolordark="#FFFFFF">
                        <tr>
                            <td colspan="3">
                                Получатель платежа: <?= $this->get_setting('receiver'); ?><br>
                                ИНН: <?= $this->get_setting('INN'); ?><br>
                                Р/c: <?= $this->get_setting('bankAccount'); ?>, <?= $this->get_setting('bankName'); ?>
                                <br>
                                Корр.сч.: <?= $this->get_setting('correspondentAccount'); ?><br>
                                КПП: <?= $this->get_setting('KPP'); ?><br>
                                БИК: <?= $this->get_setting('BIK'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td COLSPAN="3"><br>
                                <br>
                                <hr size="1" color="#000000">
                                <div align="center"
                                     style="font-family: sans-serif; font-size: xx-small">фамилия, и.о., адрес
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td ALIGN="center">Вид платежа</td>
                            <td ALIGN="center">Дата</td>
                            <td ALIGN="center">Сумма</td>
                        </tr>
                        <tr>
                            <td ALIGN="left"><?= $invoice->get_description(); ?></td>
                            <td valign="bottom">__________</td>
                            <td valign="bottom"><?= $invoice->get_amount(); ?></td>
                        </tr>
                        <tr>
                            <td ALIGN="left" ROWSPAN="2" colspan="3" valign="center">Плательщик:</td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    <?
    }

    public function on_response(nc_payment_invoice $invoice = null) {
    }

    public function validate_payment_request_parameters() {
    }

    public function validate_payment_callback_response(nc_payment_invoice $invoice = null) {
    }

}