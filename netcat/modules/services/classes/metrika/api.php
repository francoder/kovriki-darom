<?php

class nc_services_metrika_api
{

    public $metrika_api_url = "http://api-metrika.yandex.ru/";
    public $req_data = array();
    public $post_data = array();
    public $token = "";
    public $method = "";
    public $error;
    public $result;

    public function __construct()
    {

        require_once realpath(dirname(__FILE__) . '/../../lib') . DIRECTORY_SEPARATOR . "Requests.php";
        Requests::register_autoloader();
    }

    public function set_token($token)
    {
        $this->token = $token;
    }

    public function reset_data()
    {
        $this->result = null;
        $this->error = "";
        $this->method = "";
        $this->req_data = array();
        $this->post_data = array();
    }

    public function set_method($value = '')
    {
        $this->method = $value;
    }

    public function set_request_data($key, $value = '')
    {
        $this->req_data[$key] = $value;
    }

    public function set_post_data($post_data)
    {
        $this->post_data = $post_data;
    }

    public function send_request($method = "get")
    {
        $this->req_data['oauth_token'] = $this->token;

        switch ($method) {
            case "delete":
                $response = Requests::delete(
                    $this->metrika_api_url . $this->method . ".json", 
                  array(
                    'Authorization' => 'OAuth '.$this->token,
                    'Accept' => 'application/x-yametrika+json')
                  );
                break;            
            case "post":
                $response = Requests::post(
                    $this->metrika_api_url . $this->method, 
                  array(
                    'Authorization' => 'OAuth '.$this->token, 
                    'Accept' => 'application/x-yametrika+json', 
                    'Content-Type' => 'application/x-yametrika+json'), 
                  json_encode($this->post_data)
                );
                break;
            case "get":
                $response = Requests::get(
                    $this->metrika_api_url . $this->method . ".json?pretty=1&" . http_build_query($this->req_data), 
                  array('Accept' => 'application/x-yametrika+json'));
                break;
        }

        if ($response->success == 1) {
            $this->result = json_decode($response->body, true);
            if (isset($this->result)) {
                return true;
            } else if (!empty($this->result->errors)) {
                foreach ($this->result->errors as $error) {
                    $this->error .= "Error: code = " . $error->code . ", detail = " . $error->text;
                }
            } else {
                $this->error = "Unknown error";
            }
        } else {
            $this->error = "Error: code = " . $response->status_code;
        }
        return false;
    }

}
