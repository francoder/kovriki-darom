<?php

/**
 * Класс для интеграции с KKM Server
 */
class nc_payment_register_provider_kkmserver extends nc_payment_register_provider {

    static protected $settings = array(
        // Название организации / заголовок чека
        'PaymentRegisterKkmReceiptHeader' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_HEADER,
        // URL KKM Server с указанием протокола и порта, например 'http://210.24.12.66:5893':
        'PaymentRegisterKkmServerUrl' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_URL,
        // Логин пользователя
        'PaymentRegisterKkmServerLogin' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_LOGIN,
        // Пароль пользователя
        'PaymentRegisterKkmServerPassword' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_PWD,
        // Номер устройства. Если 0 то первое не блокированное на сервере:
        'PaymentRegisterKkmServerDeviceNumber' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_NUMDEVICE,
        // ИНН ККМ для поиска. Если "", то ККМ ищется только по DeviceNumber;
        // если DeviceNumber = 0, а PaymentRegisterKkmServerDeviceInn заполнено,
        // то ККМ ищется только по DeviceInn:
        'PaymentRegisterKkmServerDeviceInn' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_KKMINN,
        // Заводской номер ККМ для поиска. Если "", то ККМ ищется только по DeviceNumber:
        'PaymentRegisterKkmServerDeviceSerial' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_KKMNUM,
        // Время (сек) ожидания выполнения команды:
        'PaymentRegisterKkmServerTimeout' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_TIMEOUT,
        // Продавец, тег ОФД 1021:
        'PaymentRegisterKkmServerCashierName' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_CASHIER,
        // Время закрытия смены
        'PaymentRegisterKkmServerShiftStartTime' => NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_SHIFT_START_TIME,
    );

    static protected $kkm_vat_enum = array(
        '' => -1,
        0 => 0,
        10 => 10,
        18 => 18,
    );
    static protected $kkm_default_vat = 18;

    /** @var bool защита от зацикливания при попытке открытия смены */
    protected $is_trying_to_reopen_shift = false;

    /**
     * Обработка нового чека (отправка запроса на создание чека в ККМ)
     *
     * @param nc_payment_receipt $receipt
     */
    public function process_receipt(nc_payment_receipt $receipt) {
        @set_time_limit(0);
        @ignore_user_abort(true);

        $invoice = $receipt->get_invoice();

        $receipt_type = $receipt->get('operation') === nc_payment::OPERATION_SELL ? 0 : 1;
        $items = $receipt->get_items();
        $totals = sprintf('%0.2F', $items->sum('total_price'));

        /* start settings */
        $timeout = $this->get_setting('PaymentRegisterKkmServerTimeout') ?: 30;

        $tax_array = array(
            'osn' => 0,
            'usn_income' => 1,
            'usn_income_outcome' => 2,
            'envd' => 3,
            'esn' => 4,
            'patent' => 5
        );
        $tax_variant = $tax_array[$this->get_setting('PaymentRegisterSN')];
        /* end settings */

        $customer_contact = $invoice->get_customer_contact_for_receipt();

        $data = array(
            'Command' => 'RegisterCheck',
            'Timeout' => $timeout,                              // Время (сек) ожидания выполнения команды.
            'IsFiscalCheck' => true,                            // Это фискальный или не фискальный чек
            'TypeCheck' => $receipt_type,                       // Тип чека
            'CancelOpenedCheck' => true,                        // Аннулировать открытый чек если ранее чек не был завершен до конца
            'NotPrint' => false,                                // Не печатать чек на бумагу
            'NumberCopies' => 0,                                // Количество копий документа
            'CashierName' => $this->get_setting('PaymentRegisterKkmServerCashierName'), // Продавец, тег ОФД 1021
            'ClientAddress' => $customer_contact,               // Телефон или е-Майл покупателя, тег ОФД 1008
            'TaxVariant' => $tax_variant,                       // Система налогообложения (СНО) применяемая для чека
            // 'KPP' => '',                                     // КПП организации, нужно только для ЕГАИС
            // Строки чека
            'CheckStrings' => array(
                array(
                    'PrintText' => array(
                        'Text' => '>#2#<' . $this->get_setting('PaymentRegisterKkmReceiptHeader'), // При вставке в текст символов ">#10#<" строка при печати выровняется по центру, где 10 - это на сколько меньше станет строка ККТ
                        'Font' => 1,
                    ),
                ),
                array(
                    'PrintText' => array(
                        'Text' => '',
                        'Font' => 1,
                    ),
                ),
                array(
                    'PrintText' => array(
                        'Text' => 'Номер заказа: ' . $invoice->get('order_id') ?: $invoice->get_id(),
                        'Font' => 1,
                    ),
                ),
                array(
                    'PrintText' => array(
                        'Text' => '',
                        'Font' => 1,
                    ),
                ),
            ),
            'Cash' => 0, // Наличная оплата
            'CashLessType1' => $totals, // Безналичная оплата типа 1 (по умолчанию - Оплата картой)
            'CashLessType2' => 0, // Безналичная оплата типа 2 (по умолчанию - Оплата кредитом)
            'CashLessType3' => 0, // Безналичная оплата типа 3 (по умолчанию - Оплата сертификатом)
        );

        $goods_data = array();
        foreach ($items as $item) {
            $goods_data[] = array(
                'Register' => array(
                    // Наименование товара 64 символа:
                    'Name' => nc_substr($item->get('name'), 0, 64),
                    // Количество товара:
                    'Quantity' => intval($item->get('qty')),
                    // Цена за шт. без скидки [информации о размере скидки в nc_payment_invoice_items нет]:
                    'Price' => sprintf('%0.2F', $item->get('item_price')),
                    // Конечная сумма строки с учетом всех скидок/наценок:
                    'Amount' => sprintf('%0.2F', $item->get('total_price')),
                    // Отдел, по которому ведется продажа:
                    'Department' => 0,
                    // НДС в процентах или ТЕГ НДС: 0 (НДС 0%), 10 (НДС 10%), 18 (НДС 18%), -1 (НДС не облагается), 118 (НДС 18/118), 110 (НДС 10/110):
                    'Tax' => nc_array_value(self::$kkm_vat_enum, $item->get('vat_rate'), self::$kkm_default_vat),
                ),
            );
        }

        $data['CheckStrings'] = array_merge($data['CheckStrings'], $goods_data);
        $data['CashLessType1'] = $totals;

        $result = $this->execute_request($data);

        $is_error =
            !isset($result['Status']) ||
            !empty($result['Error']) ||
            $result['Status'] != 0 ||
            !empty($result['ConnectionError']);

        if ($is_error) {
            $result_error_message = nc_array_value($result, 'Error', '');
            // "Смена не открыта или смена превысила 24 часа – операция невозможна"; "(Не выполнен вход из режима ( 136 : Смена превысила 24 часа ))"
            // пробуем открыть смену и снова отправить чек
            // (сообщение и исходный код всегда в UTF8, поэтому используем mb_stripos(), а не nc_stripos())
            if (!$this->is_trying_to_reopen_shift && mb_stripos($result_error_message, 'Смена', 0, 'UTF-8') !== false) {
                if ($this->reopen_shift()) {
                    $this->is_trying_to_reopen_shift = true;
                    $this->process_receipt($receipt);
                    return;
                }
            }

            $receipt_status = !empty($result['ConnectionError']) ? $receipt::STATUS_CONNECTION_ERROR : $receipt::STATUS_FAILED;
        } else {
            // В старых версиях KKMServer дополнительные данные о чеке возвращались в элементе URL,
            // начиная с версии от 14.08.2017 — в QRCode.
            // t-дата-время, s-сумма документа, fn-номер ФН, i-номер документа, fp-фискальная подпись, n-тип документа
            $meta_string = !empty($result['QRCode']) ? $result['QRCode'] : $result['URL'];
            parse_str($meta_string, $meta);

            $receipt_datetime = DateTime::createFromFormat('Ymd\THis', nc_array_value($meta, 't'));
            $receipt->set_values(array(
                'fiscal_receipt_created' => $receipt_datetime ? $receipt_datetime->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                'fiscal_receipt_number' => $result['CheckNumber'],
                'shift_number' => $result['SessionNumber'],
                'fiscal_storage_number' => nc_array_value($meta, 'fn'),
                'register_registration_number' => $this->get_kkm_registration_number(),
                'fiscal_document_number' => nc_array_value($meta, 'i'),
                'fiscal_document_attribute' => nc_array_value($meta, 'fp'),
            ));
            $receipt_status = $receipt::STATUS_REGISTERED;
        }

        $receipt->save_status(
            $receipt_status,
            array(
                'request' => $data,
                'response' => $result,
            )
        );
    }

    /**
     * @return bool
     */
    protected function reopen_shift() {
        $response = $this->execute_request(array(
            'Command' => 'ZReport',
        ));

        $is_error = (bool)nc_array_value($response, 'Error', false);
        nc_payment_register::log(array(
            'Catalogue_ID' => $this->site_id,
            'EventType' => $is_error ? nc_payment_register::LOG_TYPE_ERROR : nc_payment_register::LOG_TYPE_EVENT,
            'Message' => NETCAT_MODULE_PAYMENT_REGISTER_LOG_Z_REPORT,
            'AdditionalData' => $response,
        ));

        if ($is_error) {
            return false;
        }

        $response = $this->execute_request(array(
            'Command' => 'OpenShift',
            'CashierName' => $this->get_setting('PaymentRegisterKkmServerCashierName'),
        ));

        $is_error = (bool)nc_array_value($response, 'Error', false);
        nc_payment_register::log(array(
            'Catalogue_ID' => $this->site_id,
            'EventType' => $is_error ? nc_payment_register::LOG_TYPE_ERROR : nc_payment_register::LOG_TYPE_EVENT,
            'Message' => NETCAT_MODULE_PAYMENT_REGISTER_LOG_OPEN_SHIFT,
            'AdditionalData' => $response,
        ));

        if ($is_error) {
            return false;
        }

        return true;
    }

    /**
     * @return null
     */
    protected function get_kkm_registration_number() {
        $data = $this->execute_request(array(
            'Command' => 'GetDataKKT',
        ));
        if (isset($data['Info']['RegNumber'])) {
            return $data['Info']['RegNumber'];
        }
        return null;
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function execute_request($data = array()) {
        $num_device = $this->get_setting('PaymentRegisterKkmServerDeviceNumber') ?: 0;
        $inn_kkm = $this->get_setting('PaymentRegisterKkmServerDeviceInn') ?: '';
        $kkt_number = $this->get_setting('PaymentRegisterKkmServerDeviceSerial') ?: '';

        $data = array_merge(array(
            'NumDevice' => $num_device, // Номер устройства. Если 0 то первое не блокированное на сервере
            'InnKkm' => $inn_kkm,       // ИНН ККМ для поиска. Если "" то ККМ ищется только по PaymentRegisterKkmServerDeviceNumber, если PaymentRegisterKkmServerDeviceNumber = 0 а PaymentRegisterKkmServerDeviceInn заполнено то ККМ ищется только по PaymentRegisterKkmServerDeviceInn
            'KktNumber' => $kkt_number, // Заводской номер ККМ для поиска. Если "" то ККМ ищется только по NumDevice,
        ), $data);

        $ch = curl_init($this->get_setting('PaymentRegisterKkmServerUrl') . '/Execute/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->get_setting('PaymentRegisterKkmServerLogin')) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->get_setting('PaymentRegisterKkmServerLogin') . ':' . $this->get_setting('PaymentRegisterKkmServerPassword'));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, nc_array_json($data));

        // KKMServer генерирует сертификаты, достоверность которых, естественно, не может быть проверена
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (!json_last_error()) {
                return $result;
            } else {
                return array('Error' => 'JSON error: ' . json_last_error_msg(), 'Response' => $response);
            }
        } else if ($curl_error) {
            return array('Error' => $curl_error, 'ConnectionError' => true);
        } else {
            return array('Error' => '(empty response)', 'ConnectionError' => true);
        }
    }

    /**
     * Выполнение задач при периодическом запуске скрипта из планировщика:
     * Z-отчёт и открытие новой смены
     */
    public function execute_cron_tasks() {
        $shift_start_time = trim($this->get_setting('PaymentRegisterKkmServerShiftStartTime'));
        if (!strlen($shift_start_time)) {
            return;
        }

        $current_shift_setting = 'PaymentRegisterKkmServerShiftStart_' . $this->get_kkm_id();

        $current_timestamp = time();
        $today_date = date("Y-m-d", $current_timestamp);
        $reopen_timestamp = strtotime("today $shift_start_time") - 10;

        $shift_start_timestamp = (int)$this->get_setting($current_shift_setting);
        $shift_start_date = date("Y-m-d", $shift_start_timestamp);
        $shift_end_timestamp = $shift_start_timestamp + 60*60*24 - 10;

        // смена длится больше 24 ч; или смена была открыта не сегодня и подошло время открытия смены
        if ($current_timestamp >= $shift_end_timestamp || ($today_date != $shift_start_date && $current_timestamp >= $reopen_timestamp)) {
            if ($this->reopen_shift()) {
                nc_core::get_object()->set_settings($current_shift_setting, $reopen_timestamp, 'payment', $this->site_id);
            }
        }
    }

    /**
     * Возвращает строку, которая позволяет сопоставлять ККМ на разных серверах
     * (понять, что на разных сайтах используется одна и та же ККМ)
     * @return string
     */
    protected function get_kkm_id() {
        $settings_for_identification = array(
            'PaymentRegisterKkmServerUrl',
            'PaymentRegisterKkmServerDeviceNumber',
            'PaymentRegisterKkmServerDeviceInn',
            'PaymentRegisterKkmServerDeviceSerial',
        );

        $values = array();
        foreach ($settings_for_identification as $key) {
            $values[] = $this->get_setting($key);
        }

        return md5(join("\n", $values));
    }

}
