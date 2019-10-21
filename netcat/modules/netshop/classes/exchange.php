<?php

/**
 * nc_netshop_exchange
 * Содержит общую для обмена функциональность.
 */
class nc_netshop_exchange {

    /**
     * Url к файлу, запускающему по cron'у задачи
     * @return string
     */
    public static function get_cron_script_url() {
        return nc_module_path() . 'netshop/exchange/cron.php';
    }

}
