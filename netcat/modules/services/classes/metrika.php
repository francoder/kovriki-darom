<?php

class nc_services_metrika
{

    public $auth_token;
    
    protected $allowed_objects = array(
      'counter' => true, 
      'stat' => true, 
      );

    /**
     * Constructor
     * @param nc_services $services
     */
    public function __construct(nc_services $services)
    {
        $this->auth_token = nc_core()->get_settings("yandex_auth_token", "services", false, 0);
    }

    /**
     * Обеспечивает загрузку «подмодулей» по запросу
     * @param $sub_module_name
     * @return null|object
     */
    public function __get($sub_module_name)
    {
        if (!isset($this->allowed_objects[$sub_module_name])) {
            return null;
        }
        if ($this->allowed_objects[$sub_module_name] === true) {
            $class_name = "nc_services_metrika_" . $sub_module_name;
            $this->allowed_objects[$sub_module_name] = new $class_name($this);
        }
        return $this->allowed_objects[$sub_module_name];
    }

}
