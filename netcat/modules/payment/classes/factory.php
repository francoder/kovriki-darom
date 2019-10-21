<?

/**
 * Фабрика создания объекта платежной системы с использованием данных NetCat
 *
 * @original_author vitaliy
 *
 */
class nc_payment_factory extends nc_payment_factory_abstract {

    static protected $id_to_class = array();
    static protected $class_to_id = array();

    /** @var nc_payment_system[] */
    static protected $instances = array();

    /**
     * Создание объекта платежной системы по названию класса или ID записи в
     * списке (классификаторе) PaymentSystem
     *
     * @param    string|int $system
     * @param    int|null $site_id
     * @return   nc_payment_system|null
     */
    public static function create($system, $site_id = null) {
        $db = nc_db();
        if (is_numeric($system)) {
            $system_id = (int)$system;
            if (!isset(self::$id_to_class[$system_id])) {
                self::$id_to_class[$system_id] = $db->get_var(
                    "SELECT `Value`
                       FROM `" . NC_PAYMENT_SYSTEM_CLASSIFIER_TABLE . "`
                      WHERE `PaymentSystem_ID` = {$system_id}"
                ) ?: '';
            }
            $system_class = self::$id_to_class[$system_id];
        }
        else {
            $system_class = $system;
            if (!isset(self::$class_to_id[$system_class])) {
                self::$class_to_id[$system_class] = (int)$db->get_var(
                    "SELECT `PaymentSystem_ID`
                       FROM `" . NC_PAYMENT_SYSTEM_CLASSIFIER_TABLE . "`
                      WHERE `Value` = '" . $db->escape($system_class) . "'"
                );
            }
            $system_id = self::$class_to_id[$system_class];
        }

        self::$id_to_class[$system_id] = $system_class;
        self::$class_to_id[$system_class] = $system_id;

        if (!$system_id || !$system_class) {
            return null;
        }

        $site_id = (int)$site_id;
        if (!$site_id) {
            $site_id = (int)nc_core::get_object()->catalogue->get_current("Catalogue_ID");
        }

        $instance_key = "$site_id:$system_id";
        if (!isset(self::$instances[$instance_key])) {
            $payment_system = parent::create($system_class);
            $payment_system->set_id($system_id);
            self::set_system_params($payment_system, $site_id);
            self::$instances[$instance_key] = $payment_system;
        }

        return self::$instances[$instance_key];
    }

    /**
     * Загрузка параметров платежной системы для текущего сайта
     *
     * @param nc_payment_system $payment_system
     * @param int $site_id
     */
    protected static function set_system_params(nc_payment_system $payment_system, $site_id) {
        $system_id = (int)$payment_system->get_id();

        $results = (array)nc_db()->get_results("SELECT `Param_Name`, `Param_Value`
                                                  FROM `" . NC_PAYMENT_SYSTEM_PARAM_TABLE . "`
                                                 WHERE `System_ID`='$system_id'
                                                   AND `Catalogue_ID` = $site_id", ARRAY_A);

        $params = array();
        foreach($results as $row) {
            $params[$row['Param_Name']] = $row['Param_Value'];
        }

        $payment_system->set_settings($params);
    }

    /**
     * Функция возвращает массив с названиями классов включенных платежных систем
     * на сайте $catalogue, или, если $catalogue не указан, список классов платежных
     * систем из списка (классификатора) PaymentSystem.
     *
     * @param    integer $site_id
     * @return   array
     */
    public static function get_available_payment_systems($site_id = NULL) {
        /** @var nc_db $db */
        $db = nc_Core::get_object()->db;
        $site_id = (int)$site_id;

        $query = "SELECT b.*
                    FROM `" . NC_PAYMENT_SYSTEM_CATALOGUE_TABLE . "` AS a
                    LEFT JOIN `" . NC_PAYMENT_SYSTEM_CLASSIFIER_TABLE . "` AS b
                         ON (a.`PaymentSystem_ID` = b.`PaymentSystem_ID`)";

        if ($site_id) {
            $query .= "WHERE a.`Catalogue_ID` = $site_id AND a.`Checked`=1";
        }

        return (array)$db->get_results($query, ARRAY_A);
    }

}
