<?php

class nc_netshop_exchange_handler {
    /* @var nc_netshop_exchange_object $object */
    protected $object = null;
    protected $file_name = 'exchange_handler.txt';

    public function __construct(nc_netshop_exchange_object $exchange_object) {
        $this->object = $exchange_object;
    }

    public function get_path() {
        return $this->object->get_folder_path() . '/' . $this->file_name;
    }

    public function has() {
        return file_exists($this->get_path()) && !is_dir($this->get_path());
    }

    public function get($key, $default = null) {
        $data = $this->get_data();
        return isset($data[$key]) ? $data[$key] : $default;
    }

    public function get_data() {
        $data = null;
        if ($this->has()) {
            $data = file_get_contents($this->get_path());
            if ($data) {
                $data = json_decode($data, true);
            }
        }
        if (!$data) {
            $data = array();
        }
        return $data;
    }

    public function save(array $data = array()) {
        file_put_contents($this->get_path(), nc_array_json($data));
    }

    public function create(array $data = array()) {
        $this->save($data);
    }

    public function remove() {
        if ($this->has()) {
            unlink($this->get_path());
        }
    }
}