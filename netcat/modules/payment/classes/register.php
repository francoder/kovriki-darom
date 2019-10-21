<?php

/**
 * Класс для интеграции с онлайн-кассой, выдающей чеки
 */
abstract class nc_payment_register {
    const LOG_TYPE_EVENT = 200;
    const LOG_TYPE_ERROR = 400;

    /**
     * @param $site_id
     * @return bool
     */
    static public function is_enabled($site_id = null) {
        return (bool)nc_payment::get_setting('PaymentRegisterChecked', $site_id);
    }

    /**
     * Адрес электронной почты, который используется, когда неизвестен и телефон,
     * и адрес клиента.
     * См. также nc_payment_invoice::get_customer_contact_for_receipt()
     */
    static public function get_default_customer_email() {
        return 'unclaimed-receipts@' . $_SERVER['HTTP_HOST'];
    }

    /**
     * @param null $site_id
     * @return mixed
     */
    static protected function get_sender_email($site_id = null) {
        return nc_core::get_object()->get_settings('SpamFromEmail', 'system', false, $site_id);
    }

    /**
     * @param null $site_id
     * @return int|null
     */
    static public function get_provider_id($site_id = null) {
        if (!self::is_enabled($site_id)) {
            return null;
        }
        return (int)nc_payment::get_setting('PaymentRegisterProviderID', $site_id);
    }

    /**
     * @param null $site_id
     * @return nc_payment_register_provider|null
     */
    static public function get_provider_instance($site_id = null) {
        $nc_core = nc_core::get_object();
        if (!(int)$site_id) {
            $site_id = null;
        }

        $register_provider_id = self::get_provider_id($site_id);
        if (!$register_provider_id) {
            return null;
        }

        $class_name = $nc_core->db->get_var("SELECT `Value` FROM `Classificator_PaymentRegister` WHERE `PaymentRegister_ID` = '$register_provider_id'");
        if (is_subclass_of($class_name, 'nc_payment_register_provider')) {
            $instance = new $class_name($site_id, $register_provider_id);
            return $instance;
        } else {
            trigger_error(__METHOD__ . ": '$class_name' must be a descendant of nc_payment_register_provider", E_USER_WARNING);
            return null;
        }
    }

    /**
     * @param nc_payment_invoice $invoice
     * @return array
     */
    static public function get_register_provider_type_and_id(nc_payment_invoice $invoice) {
        $site_id = $invoice->get('site_id');
        $payment_system_id = $invoice->get('payment_system_id');
        $payment_system = nc_payment_factory::create($payment_system_id, $site_id);

        $use_payment_system_for_receipts =
            $payment_system && (
                // автоматические возвраты через платёжную систему пока не обрабатываются
                ($invoice->get('type') == nc_payment_invoice::TYPE_PRIMARY && $payment_system->can_send_receipt_data_with_invoice()) ||
                $payment_system->can_send_custom_receipts()
            );

        if ($use_payment_system_for_receipts) {
            $register_provider_type = nc_payment_receipt::PROVIDER_TYPE_SYSTEM;
            $register_provider_id = $payment_system_id;
        } else {
            $register_provider_type = nc_payment_receipt::PROVIDER_TYPE_REGISTER;
            $register_provider_id = nc_payment_register::get_provider_id($site_id);
        }

        return array($register_provider_type, $register_provider_id);
    }

    /**
     * Отправляет чек в кассовый сервис или платёжную систему
     * @param nc_payment_receipt $receipt
     */
    static public function route_receipt_processing(nc_payment_receipt $receipt) {
        /** @var nc_payment_invoice $invoice */
        $invoice = $receipt->get_invoice();

        if ($invoice->get('status') != nc_payment_invoice::STATUS_SUCCESS) {
            return;
        }

        $site_id = $invoice->get('site_id');

        list($register_provider_type, $register_provider_id) = self::get_register_provider_type_and_id($invoice);
        $receipt->set_values(array(
            'register_provider_type' => $register_provider_type,
            'register_provider_id' => $register_provider_id,
        ));

        if ($register_provider_type == nc_payment_receipt::PROVIDER_TYPE_SYSTEM) {
            $payment_system = nc_payment_factory::create($invoice->get('payment_system_id'), $site_id);
            if ($payment_system instanceof nc_payment_system) {
                $payment_system->process_receipt($receipt);
            }
        } else {
            $register = nc_payment_register::get_provider_instance($site_id);
            if ($register instanceof nc_payment_register_provider) {
                $register->process_receipt($receipt);
            }
        }
    }

    /**
     * @param array $data
     */
    static public function log(array $data) {
        if (!$data['Catalogue_ID']) {
            $data['Catalogue_ID'] = nc_core::get_object()->catalogue->get_current('Catalogue_ID');
        }
        if (is_array($data['AdditionalData'])) {
            $data['AdditionalData'] = serialize($data['AdditionalData']);
        }
        nc_db_table::make('Payment_RegisterLog')->insert($data);
    }

    /**
     * @param string $error_description
     * @param nc_payment_receipt|null $receipt
     */
    static public function send_receipt_warning_to_admin($error_description, nc_payment_receipt $receipt) {
        $nc_core = nc_core::get_object();
        $invoice = $receipt->get_invoice();
        $site_id = $invoice->get('site_id') ?: $nc_core->catalogue->get_current('Catalogue_ID');

        $to = nc_payment::get_setting('PaymentRegisterWarningsEmail', $site_id);
        $from = $reply = self::get_sender_email($site_id);
        if (!$to || !$from) {
            return;
        }

        $receipt_link = $nc_core->catalogue->get_url_by_id($site_id) .
                        $nc_core->ADMIN_PATH .
                        '#module.payment.receipt.view(' . $receipt->get_id() . ')';

        $subject = NETCAT_MODULE_PAYMENT_REGISTER_MAIL_ADMIN_WARNING_SUBJECT;
        $message = NETCAT_MODULE_PAYMENT_REGISTER_MAIL_ADMIN_WARNING_BODY_INTRO . "\n" .
                   $error_description . "\n\n" .
                   NETCAT_MODULE_PAYMENT_REGISTER_MAIL_ADMIN_WARNING_BODY_LINK . "\n" .
                   $receipt_link . "\n";

        $nc_core->mail->mailbody($message);
        $nc_core->mail->send($to, $from, $reply, $subject);
    }

    /**
     * @param nc_payment_receipt $receipt
     */
    static public function send_receipt_to_customer(nc_payment_receipt $receipt) {
        $nc_core = nc_core::get_object();
        $invoice = $receipt->get_invoice();
        $site_id = $invoice->get('site_id') ?: $nc_core->catalogue->get_current('Catalogue_ID');

        $send_email = nc_payment::get_setting('PaymentRegisterEmailReceipt', $site_id);
        $to = $invoice->get('customer_email');
        $from = $reply = self::get_sender_email($site_id);
        if (!$send_email || !$to || !$from) {
            return;
        }

        $receipt_body = self::make_receipt_html($receipt);
        $subject = NETCAT_MODULE_PAYMENT_REGISTER_MAIL_RECEIPT_SUBJECT;

        $plain_text = $receipt_body;
        $plain_text = preg_replace('/^[ ]+/m', '', $plain_text); // только пробелы
        $plain_text = strip_tags($plain_text); // в таком порядке!

        $nc_core->mail->mailbody($plain_text, $receipt_body);
        $nc_core->mail->send($to, $from, $reply, $subject);

    }

    /**
     * @param nc_payment_receipt $receipt
     * @return string
     */
    static public function make_receipt_html(nc_payment_receipt $receipt) {
        $nc_core = nc_core::get_object();
        $invoice = $receipt->get_invoice();
        $site_id = $invoice->get('site_id') ?: $nc_core->catalogue->get_current('Catalogue_ID');

        $utf = $nc_core->NC_UNICODE
            ? function($string) { return $string; }
            : function($string) use ($nc_core) { return $nc_core->utf8->win2utf($string); };

        $company_name = $utf(nc_payment::get_setting('PaymentRegisterCompanyName', $site_id));
        $inn = nc_payment::get_setting('PaymentRegisterINN', $site_id);
        $type = $utf(nc_payment_receipt_admin_helpers::get_operation_string($receipt));
        $n = $receipt->get('fiscal_receipt_number');
        $amount = nc_payment_admin_helpers::format_number($receipt->get('amount'));
        $time = nc_payment_admin_helpers::format_time($receipt->get('fiscal_receipt_created'));

        $tax_system = nc_payment::get_setting('PaymentRegisterSN');
        $tax_system_name = $utf(nc_array_value(self::get_tax_system_names(), $tax_system, '-'));
        $tax_system_name = nc_strtolower($tax_system_name);

        // Форматирование частично учитывает возможность отправки письма в виде plain-text
        $invisible_colon = '<span style="display: none">:</span>';
        $table_attributes = ' style="margin: 10px -4px" cellpadding="4" cellspacing="0" width="100%" border="0"';
        $items_header =
            '<tr>' .
            '<th>№  </th>' .
            '<th align="left">Наименование              </th>' .
            '<th align="right"><nobr>Цена за ед.</nobr>   </th>' .
            '<th align="right">Кол.   </th>' .
            '<th align="right">Сумма</th>' .
            '</tr>' . "\n";

        $items_rows = '';
        $vat_amounts = array();
        /** @var nc_payment_invoice_item $item */
        $i = 1;

        $spaces = str_repeat(' ', 30);

        foreach ($receipt->get_items() as $item) {
            $items_rows .= $utf(
                "<tr align='right' valign='top'><td>$i. </td>" .
                "<td>{$item->get('name')} </td>\n" .
                "<td align='right'>$spaces<nobr>" . nc_payment_admin_helpers::format_number($item->get('item_price')) . "</nobr></td>" .
                "<td align='right'><nobr><span style='display:none'>  x  </span>" . $item->get('qty') . "<span style='display:none'> =  </span></nobr></td>" .
                "<td align='right'><nobr>" . nc_payment_admin_helpers::format_number($item->get('total_price')) . "</nobr> </td>" .
                "</tr>\n"
            );

            $item_vat_rate = $item->get('vat_rate');
            if (strlen($item_vat_rate)) {
                $item_vat_amount = $item->get('total_vat_amount');
                $vat_amounts[$item_vat_rate] = nc_array_value($vat_amounts, $item_vat_rate, 0) + $item_vat_amount;
                $items_rows .=
                    "<tr><td>   </td><td colspan='3'>НДС $item_vat_rate%$invisible_colon </td><td align='right'><nobr>" .
                    nc_payment_admin_helpers::format_number($item_vat_amount) .
                    "</nobr></td></tr>\n";
            }

            $i++;
        }

        $taxes_table = '';
        if ($vat_amounts) {
            ksort($vat_amounts, SORT_NUMERIC);
            $taxes_table = "<table $table_attributes>";
            foreach ($vat_amounts as $vat_rate => $vat_amount) {
                $taxes_table .=
                    "<tr><td>НДС итога чека со ставкой $vat_rate%$invisible_colon </td>" .
                    '<td align="right"><nobr>' . nc_payment_admin_helpers::format_number($vat_amount) . "</nobr></td></tr>\n";
            }
            $taxes_table .= '</table>';
        }

        $receipt_body = <<<RECEIPT
<div style="margin: 10px; padding: 30px; max-width: 500px; background: #EEE;">
    <div style="text-align: center">$company_name</div>
    <div style="text-align: center">ИНН: $inn</div>
    
    <div style="text-align: center; margin: 10px 0; font-size: 120%; font-weight: bold">Кассовый чек №$n ($type)</div>
    <table $table_attributes>
        $items_header
        $items_rows
    </table><hr noshade size="1" color="#000"><table $table_attributes>
        <tr><td style="font-size: 120%">ИТОГО$invisible_colon </td><td align="right" style="font-size: 120%"><nobr>$amount</nobr></td></tr>
        <tr><td>Электронными$invisible_colon </td><td align="right"><nobr>$amount</nobr></td></tr>
    </table><hr noshade size="1" color="#000">
    $taxes_table
    <div>$time</div>
    <div>Смена: {$receipt->get("shift_number")}</div>
    <div>№ ФД: {$receipt->get("fiscal_document_number")}</div>
    <div>№ ФН: {$receipt->get("fiscal_storage_number")}</div>
    <div>ФПД: {$receipt->get("fiscal_document_attribute")}</div>
    <div>РН ККТ: {$receipt->get("register_registration_number")}</div>
    <div>СНО: $tax_system_name</div>
    <div>Сайт проверки ФПД: www.nalog.ru</div>
    <div>Адрес покупателя: {$invoice->get("customer_email")}</div>
</div>
RECEIPT;

        if (!$nc_core->NC_UNICODE) {
            $receipt_body = $nc_core->utf8->utf2win($receipt_body);
        }

        return $receipt_body;
    }

    /**
     * @return array
     */
    static public function get_tax_system_names() {
        return array(
            'osn' => NETCAT_MODULE_PAYMENT_REGISTER_SN_OSN,
            'usn_income' => NETCAT_MODULE_PAYMENT_REGISTER_SN_USN_INCOME,
            'usn_income_outcome' => NETCAT_MODULE_PAYMENT_REGISTER_SN_USN_INCOME_OUTCOME,
            'envd' => NETCAT_MODULE_PAYMENT_REGISTER_SN_ENVD,
            'esn' => NETCAT_MODULE_PAYMENT_REGISTER_SN_ESN,
            'patent' => NETCAT_MODULE_PAYMENT_REGISTER_SN_PATENT,
        );
    }

    /**
     *
     */
    static public function execute_cron_tasks() {
        $nc_core = nc_core::get_object();
        foreach ($nc_core->catalogue->get_all() as $site_data) {
            $site_id = $site_data['Catalogue_ID'];
            if (!self::is_enabled($site_id)) {
                continue;
            }
            $register_provider = self::get_provider_instance($site_id);
            if ($register_provider) {
                $register_provider->resend_unsent_receipts();
                $register_provider->execute_cron_tasks();
            }
        }
    }

}
