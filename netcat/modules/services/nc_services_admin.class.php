<?php

class nc_services_admin {

    protected $core, $db;

    public function __construct() {
        $this->core = nc_Core::get_object();
        $this->db = & $this->core->db;
    }

    public function get_mainsettings_url() {
        return "#module.services";
    }
}