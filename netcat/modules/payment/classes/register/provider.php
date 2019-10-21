<?php

/**
 * Класс для интеграции с онлайн-кассой, выдающей чеки
 */
abstract class nc_payment_register_provider {

    /** @var array настройки кассы (ключ => описание), должны переопределяться в классе-наследнике */
    static protected $settings = array();
    /** @var int ID */
    protected $provider_id;
    /** @var int */
    protected $site_id;

    /**
     * @param $site_id
     * @param $provider_id
     */
    public function __construct($site_id = null, $provider_id = null) {
        $this->site_id = $site_id;
        $this->provider_id = $provider_id;
    }

    /**
     * Возвращает ID элемента списка PaymentRegister, соответствующего классу
     *
     * @return int|null
     */
    final protected function get_id() {
        if (!$this->provider_id) {
            $this->provider_id = nc_db()->get_var(
                "SELECT `PaymentRegister_ID` 
                   FROM `Classificator_PaymentRegister` 
                  WHERE `Value` = '" . get_class($this) . "'"
            );
        }
        return $this->provider_id;
    }

    /**
     * @param $setting
     * @return mixed
     */
    protected function get_setting($setting) {
        return nc_payment::get_setting($setting, $this->site_id);
    }

    /**
     * @return array
     */
    static public function get_settings_description() {
        return static::$settings;
    }

    /**
     * Обработка нового чека (отправка запроса на создание чека в ККМ)
     *
     * @param nc_payment_receipt $receipt
     */
    abstract public function process_receipt(nc_payment_receipt $receipt);

    /**
     * Обработка обратного запроса от ККМ (скрипт /register/callback.php)
     */
    public function process_callback() {
    }

    /**
     * Выполнение задач при периодическом запуске скрипта из планировщика:
     * например, закрытие и открытие смены, запрос статуса
     */
    public function execute_cron_tasks() {
    }

    /**
     *
     */
    public function resend_unsent_receipts() {
        $unsent_receipts_query =
            "SELECT * 
               FROM `Payment_Receipt` 
              WHERE `RegisterProvider_Type` = '" . nc_payment_receipt::PROVIDER_TYPE_REGISTER . "'
                AND `RegisterProvider_ID` = '" . $this->get_id() . "'
                AND `Status` = '" . nc_payment_receipt::STATUS_CONNECTION_ERROR . "'";

        $receipts = nc_payment_receipt_collection::load_records($unsent_receipts_query);
        foreach ($receipts as $receipt) {
            $this->process_receipt($receipt);
        }
    }

}
