<?php

class nc_services_metrika_counter
{

    public $api;

    /**
     * Constructor
     * @param nc_services_metrika $metrika
     */
    public function __construct(nc_services_metrika $metrika)
    {
        $this->api = new nc_services_metrika_api();
        $this->api->set_token($metrika->auth_token);
    }

    public function get_list()
    {
        $counters = array();

        $this->api->reset_data();
        $this->api->set_method("counters");
        if ($this->api->send_request() === true) {
            foreach ($this->api->result['counters'] as $counter) {
                $counter['code_status_text'] = constant("NETCAT_MODULE_SERVICES_METRIKA_" . $counter['code_status']);
                $counter['permission_text'] = constant("NETCAT_MODULE_SERVICES_METRIKA_CP_" . strtoupper($counter['permission']));
                array_push($counters, $counter);
            }
        }
        return $counters;
    }

    public function check_code($counter_id)
    {
        $this->api->reset_data();
        $this->api->set_method("counter/".$counter_id."/check");
        if ($this->api->send_request() === true) {
        }
    }

    public function get($counter_id)
    {
        $result = array();
        $this->api->reset_data();
        $this->api->set_method("counter/".$counter_id);
        if ($this->api->send_request() === true) {
            $result = $this->api->result['counter'];
        } else {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

    public function save($data)
    {
        $result = array();
        $post_data = array();
        if (intval($data['counter_id']) > 0) {
            $post_data['counter_id'] = $data['counter_id'];
        }

        $post_data['name'] = $data['name'];

        $domain = parse_url('http://' . $data['site'], PHP_URL_HOST);
        $post_data['site'] = nc_Core::get_object()->catalogue->get_scheme_by_host_name($domain) . '://' . $data['site'];

        $this->api->reset_data();
        $this->api->set_method("counters");
        $this->api->set_post_data($post_data);
        if ($this->api->send_request("post") === false) {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        } else {
            $result = $this->api->result;
        }
        return $result;
    }

    public function remove($counter_id)
    {
        $result = array();

        $this->api->reset_data();
        $this->api->set_method("counter/".$counter_id);
        if ($this->api->send_request("delete") === false) {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

}
