<?php

/**
 * nc_netshop_exchange_session
 * Класс для управления данными в сессии в рамках модуля обмена.
 */
class nc_netshop_exchange_session {
    /**
     * Единый префикс для всех переменных в сессии
     */
    const PREFIX = 'nc_netshop_exchange_';

    /**
     * Получить значение из сессии
     * @param string $name
     * @return mixed
     */
    public static function get($name) {
        return nc_array_value($_SESSION, self::PREFIX . $name, null);
    }

    /**
     * Установить значение в сессию
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value) {
        $_SESSION[self::PREFIX . $name] = $value;
    }

    /**
     * Проверяет, установлено ли значение в сессии
     * @param string $name
     * @return bool
     */
    public static function has($name) {
        $value = self::get($name);
        return !is_null($value);
    }

    /**
     * Удаление значения из сессии
     * @param string $name
     */
    public static function delete($name) {
        unset($_SESSION[self::PREFIX . $name]);
    }
}