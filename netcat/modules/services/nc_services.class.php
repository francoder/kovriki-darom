<?php

/**
 * Accessed via __get():
 * @property nc_services_metrika $metrika
 */
class nc_services
{

    /** @var array  (значение должно быть true) */
    protected $sub_modules = array(
      'metrika' => true,
    );

    /**
     * @return nc_services
     */
    public static function get_instance()
    {
        static $instance = null;
        if (empty($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Используйте nc_services::get_instance()
     */
    protected function __construct()
    {
        
    }

    /**
     * Обеспечивает загрузку «подмодулей» по запросу
     * @param $sub_module_name
     * @return null|object
     */
    public function __get($sub_module_name)
    {
        if (!isset($this->sub_modules[$sub_module_name])) {
            return null;
        }

        if ($this->sub_modules[$sub_module_name] === true) {
            $class_name = "nc_services_" . $sub_module_name;
            $this->sub_modules[$sub_module_name] = new $class_name($this);
        }

        return $this->sub_modules[$sub_module_name];
    }

    public function utf8_convert($str='') {
        $core_charset = (nc_core()->NC_UNICODE || empty(nc_core()->NC_CHARSET)) ? "utf-8" : nc_core()->NC_CHARSET;
        if ($core_charset == 'utf-8') {
            $str = utf8_encode($str);
        } else {
            $str = iconv('ISO-8859-1', 'utf-8', iconv($core_charset, 'utf-8', $str));
        }
        return $str;
    }
}
