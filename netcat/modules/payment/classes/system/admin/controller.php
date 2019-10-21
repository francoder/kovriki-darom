<?php

class nc_payment_system_admin_controller extends nc_payment_admin_controller {

    /** @var  nc_payment_system_admin_ui */
    protected $ui_config;
    protected $ui_config_class = 'nc_payment_system_admin_ui';

    /**
     *
     */
    protected function init() {
        parent::init();
        $this->bind('edit', array('id'));
    }

    /**
     * Отображение списка платежных систем
     */
    protected function action_index() {
        $payment_systems = (array)nc_db()->get_results(
            "SELECT a.PaymentSystem_ID AS `id`, 
                        a.PaymentSystem_Name AS `name`, 
                        b.Checked AS `enabled`
              FROM `Classificator_PaymentSystem` AS a
                   LEFT JOIN `Payment_SystemCatalogue` AS b
                   ON a.PaymentSystem_ID = b.PaymentSystem_ID AND b.Catalogue_ID = $this->site_id
             ORDER BY a.`PaymentSystem_Name`",
            ARRAY_A
        );

        $this->ui_config->locationHash = "module.payment.system($this->site_id)";

        return $this->view('list')->with('payment_systems', $payment_systems);
    }

    /**
     * Включение или выключение платежной системы на сайте
     */
    protected function action_toggle() {
        $nc_core = nc_core::get_object();
        $db = $nc_core->db;

        $enable = (int)$nc_core->input->fetch_post('enable');
        $site_id = (int)$nc_core->input->fetch_post('site_id');
        $payment_system_id = (int)$nc_core->input->fetch_post('id');

        if ($enable) {
            $db->query(
                "REPLACE INTO `Payment_SystemCatalogue`
                        SET `Catalogue_ID` = $site_id,
                            `PaymentSystem_ID` = $payment_system_id,
                            `Checked` = 1"
            );
        } else {
            $db->query(
                "DELETE FROM `Payment_SystemCatalogue` 
                      WHERE `Catalogue_ID` = $site_id 
                        AND `PaymentSystem_ID` = $payment_system_id"
            );
        }

        return $this->redirect_to_action('index');
    }

    /**
     * Отображение настроек конкретной платежной системы
     *
     * @param $id
     * @return nc_ui_view
     */
    protected function action_edit($id) {
        $site_id = (int)$this->site_id;
        $id = (int)$id;

        $db = nc_db();

        list($payment_system_class, $payment_system_name) = $db->get_row(
            "SELECT `Value`, `PaymentSystem_Name` 
               FROM `Classificator_PaymentSystem`
              WHERE `PaymentSystem_ID` = $id",
            ARRAY_N
        );

        $setting_values = (array)nc_db()->get_col(
            "SELECT `Param_Name`, `Param_Value` 
               FROM `Payment_SystemSetting` 
              WHERE `Catalogue_ID` = $site_id 
                AND `System_ID` = $id",
            1, 0
        );

        /** @var nc_payment_system $instance */
        $instance = new $payment_system_class;
        $settings = array();
        foreach ($instance->get_settings_list() as $setting) {
            $settings[$setting] = nc_array_value($setting_values, $setting);
        }

        $this->ui_config->set_location_hash("system.edit($this->site_id,$id)");
        $this->ui_config->subheaderText = $payment_system_name;
        $this->ui_config->add_submit_button();
        $this->ui_config->add_cancel_button(
            "#module.payment.system($this->site_id)",
            NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_TO_SYSTEM_LIST
        );

        return $this->view('edit', array(
            'id' => $id,
            'site_id' => $site_id,
            'settings' => $settings,
            'payment_system_name' => $payment_system_name,
        ));
    }

    /**
     * Сохранение настроек конкретной платежной системы
     */
    protected function action_save() {
        $site_id = (int)$this->site_id;
        $id = (int)$this->input->fetch_get('id');

        $saved = false;
        $params = $this->input->fetch_post('params');
        if ($params) {
            $db = nc_db();

            $db->query(
                "DELETE FROM `Payment_SystemSetting`
                  WHERE `System_ID` = $id
                    AND `Catalogue_ID` = $site_id"
            );

            foreach (array_filter($params) as $param_name => $param_value) {
                $db->query(
                    "INSERT INTO `Payment_SystemSetting`
                        SET `System_ID` = $id,
                            `Catalogue_ID` = $site_id,
                            `Param_Name` = '" . $db->prepare($param_name) . "',
                            `Param_Value` = '" . $db->prepare($param_value) . "'");
            }
            $saved = true;
        }

        return $this->action_edit($id)->with('params_saved', $saved);
    }

}