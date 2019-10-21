<?php

class nc_services_settings_admin_ui extends nc_services_admin_ui {

    /**
     * @param string $current_action
     */
    public function __construct($current_action = "index") {
        parent::__construct('settings', NETCAT_MODULE_SERVICES_SETTINGS);

        $this->activeTab = $current_action;
    }


    /**
     *
     * @return string
     */
    public function to_json() {
        $current_action = $this->activeTab;

        if ($this->locationHash == 'module.services.settings') {
            if ($current_action != 'index') {
                $this->locationHash = "module.services.settings.$current_action";
            }
        }

        $this->tabs = array(
            array(
                'id'       => 'index',
                'caption'  => NETCAT_MODULE_SERVICES_SETTINGS,
                'location' => "module.services.settings",
                'group'    => "admin",
            ),
        );

        return parent::to_json();
    }

}