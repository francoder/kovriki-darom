<?php

class nc_services_settings implements ArrayAccess
{

    /** @var array  Кэш со значениями настроек */
    protected $data = array();

    /** @var array  Значения настроек по умолчанию */
    protected $defaults = array();

    public function __construct(nc_services $services)
    {
    }

    /**
     * Обеспечивает «ленивое» получение настроек модуля.
     *
     * @param mixed $key    Если передано больше одного параметра — возвращает элементы массива $key
     * @return mixed|null
     */
    public function get($key)
    {
        $result = null;

        if (!is_scalar($key)) {
            return null;
        }

        if (array_key_exists($key, $this->data)) {
            $result = $this->data[$key];
        } else if (method_exists($this, 'get_' . $key)) {
            $getter = 'get_' . $key;
            $this->data[$key] = $result = $this->$getter();
        } else {
            /** @var nc_core $nc_core */
            $nc_core = nc_core();
            $result = $nc_core->get_settings($key, 'services', false, 0);

            // (4) if there is a default value, return it
            if ($result === null && isset($this->defaults[$key])) {
                $result = $this->defaults[$key];
            }

            $this->data[$key] = $result;
        }
        // (5) no setting was found: a silent fail (NULL will be returned)
        // Return an array element?
        $num_args = func_num_args();
        if ($num_args > 1) {
            if (is_array($result)) {
                for ($i = 1; $i < $num_args; $i++) {
                    $key = func_get_arg($i);
                    if (isset($result[$key])) {
                        $result = $result[$key];
                    } else {
                        $result = null;
                        break;
                    }
                }
            } else { // not an array!
                $result = null;
            }
        }

        return $result;
    }

    /*     * ************************************************************************
      ArrayAccess interface methods
     * ************************************************************************ */

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("nc_services_settings object is read-only.");
    }

    public function offsetUnset($offset)
    {
        throw new Exception("nc_services_settings object is read-only.");
    }

}
