<?php


class nc_payment_register_admin_controller extends nc_payment_admin_controller {

    /** @var  nc_payment_admin_ui */
    protected $ui_config;
    protected $ui_config_class = 'nc_payment_register_admin_ui';

    /**
     *
     */
    protected function action_settings() {
        $site_id = $this->site_id;

        $this->ui_config->subheaderText = NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_COMMON_SETTINGS;
        $this->ui_config->activeTab = 'settings';
        $this->ui_config->set_location_hash("register($site_id)")   ;
        $this->ui_config->actionButtons[] = array(
            "id" => "submit",
            "caption" => NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_SAVE,
            "action" => "mainView.submitIframeForm()"
        );
            
        return $this->view('settings', array(
            'register_checked' => nc_payment_register::is_enabled($site_id),
            'send_email' => nc_payment::get_setting('PaymentRegisterEmailReceipt', $site_id),
            'register_provider_id' => nc_payment_register::get_provider_id($site_id),
            'register_warning_email' => nc_payment::get_setting('PaymentRegisterWarningsEmail', $site_id),
            'company_name' => nc_payment::get_setting('PaymentRegisterCompanyName', $site_id),
            'register_inn' => nc_payment::get_setting('PaymentRegisterINN', $site_id),
            'tax_system' => nc_payment::get_setting('PaymentRegisterSN', $site_id),
        ));
    }
    
    /**
     *
     */
    protected function action_save_settings() {
        $site_id = $this->site_id;
        $settings = (array)$this->input->fetch_post('settings');
        foreach ($settings as $setting_name => $setting_value) {
            $this->nc_core->set_settings($setting_name, $setting_value, 'payment', $site_id);
        }

        return $this->action_settings();
    }

    /**
     * @return nc_ui_view
     */
    protected function action_provider_settings() {
        $register_provider_id = nc_payment_register::get_provider_id($this->site_id);

        if (!$register_provider_id) {
            $this->redirect_to_action('settings');
        }

        $register_provider_name = nc_get_list_item_name('PaymentRegister', $register_provider_id);

        $this->ui_config->activeTab = 'settings';
        $this->ui_config->subheaderText = sprintf(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS, $register_provider_name);
        $this->ui_config->set_location_hash("register.provider($this->site_id)");
        $this->ui_config->add_cancel_button("#module.payment.register($this->site_id)");
        $this->ui_config->add_submit_button();

        $view = $this->view('provider_settings');
        return $view;
    }
    
    /*
     *
     */
    protected function action_save_provider_settings() {
        $site_id = $this->site_id;
        $settings = $this->input->fetch_post('settings');
        if ($settings) {
            foreach ($settings as $setting_name => $setting_value) {
                $this->nc_core->set_settings($setting_name, $setting_value, 'payment', $site_id);
            }
        }
        
        return $this->action_provider_settings()->with('params_saved', true);
    }
    
    /**
     *
     */
    protected function action_log() {
        $db = nc_db();
        $site_id = (int)$this->site_id;

        $page = (int)$this->input->fetch_get('page') ?: 1;
        $per_page = 100;
        $start = ($page - 1) * $per_page;

        $log_query =
            "SELECT SQL_CALC_FOUND_ROWS *
               FROM `Payment_RegisterLog`
              WHERE (`Catalogue_ID` = $site_id OR `Catalogue_ID` IS NULL)
              ORDER BY `RegisterLog_ID` DESC
              LIMIT $start, $per_page";

        $events = (array)$db->get_results($log_query, ARRAY_A);
        $total_log_entries = $db->get_var("SELECT FOUND_ROWS()");

        $this->ui_config->subheaderText = NETCAT_MODULE_PAYMENT_ADMIN_LOG;
        $this->ui_config->activeTab = 'log';
        $this->ui_config->set_location_hash("register.log($site_id)");

        return $this->view('log', array(
            'events' => $events,
            'total_pages' => ceil($total_log_entries / $per_page),
            'page' => $page,
            'page_url' => "?controller=" . $this->get_short_controller_name() . "&action=log&site=$site_id",
        ));
    }
    
}
