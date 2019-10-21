<?php

/**
 * nc_netshop_exchange_log
 * Содержит функциональность для логгирования обмена.
 */
class nc_netshop_exchange_log {
    // Типы логов
    const TYPE_DANGER = 'danger';
    const TYPE_WARNING = 'warning';
    const TYPE_DEFAULT = 'default';
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';

    // Типы действий
    const ACTION_ERROR = 'error';
    const ACTION_CRITICAL_ERROR = 'critical_error';
    const ACTION_TYPE_CONVERSION = 'type_conversion';
    const ACTION_LIST_INSERTION = 'list_insertion';
    const ACTION_REPORT_HAS_BEEN_SENT = 'report_has_been_sent';
    const ACTION_FILE_NOT_FOUND = 'file_not_found';
    const ACTION_OBJECT_ADDED = 'object_added';
    const ACTION_OBJECT_NOT_ADDED = 'object_not_added';
    const ACTION_OBJECT_UPDATED = 'object_updated';
    const ACTION_OBJECT_NOT_UPDATED = 'object_not_updated';

    /**
     * ID объекта обмена, с логами которого работает
     * @var mixed
     */
    private $object_id = null;
    /**
     * ID определенного акта обмена у определенного объекта обмена.
     * @var int|null
     */
    private $object_exchange_id = null;
    /**
     * Временный массив для логов, которые потом заносятся в базу
     * @var array
     */
    private $logs = array();

    public function __construct(nc_netshop_exchange_object $object) {
        $this->object_id = $object->get('exchange_id');

        $db = nc_db();

        // Генерируем уникальный ID конкретного акта обмена
        $exchange_log_id = null;
        do {
            $this->object_exchange_id = mt_rand(1, mt_getrandmax());

            $exchange_log_id = $db->get_var(
                "SELECT `ExchangeLog_ID`
                FROM `Netshop_ExchangeLog`
                WHERE `Object_Exchange_ID`='{$this->object_exchange_id}'"
            );
        } while (!empty($exchange_log_id));
    }

    /**
     * @return int|null
     */
    public function get_exchange_id() {
        return $this->object_exchange_id;
    }

    /**
     * Добавление лога в список логов текущего обмена
     * @param array $params
     * @return bool
     */
    public function add($params) {
        if (empty($params['message'])) {
            return false;
        }

        $this->logs[] = array(
            $this->object_id,
            $this->object_exchange_id,
            !empty($params['file_path']) ? $params['file_path'] : '',
            date('Y-m-d H:i:s'),
            $params['message'],
            !empty($params['type']) ? $params['type'] : self::TYPE_DEFAULT,
            !empty($params['action']) ? $params['action'] : '',
        );

        return true;
    }

    /**
     * Сохранение добавленных логов в базу
     */
    public function save() {
        $db = nc_db();
        if (empty($this->logs)) {
            return;
        }
        $query_common_part = "
            INSERT INTO `Netshop_ExchangeLog`
                (`Object_ID`,`Object_Exchange_ID`,`File_Path`,`Created`,`Message`,`Type`,`Action`)
            VALUES
        ";
        $all_logs = $this->logs;
        $logs_parts = array_chunk($all_logs, 250);
        foreach ($logs_parts as $logs_part) {
            foreach ($logs_part as &$log) {
                foreach ($log as &$val) {
                    $val = "'" . $db->prepare($val) . "'";
                }
                unset($val);
                $log = '(' . implode(',', $log) . ')';
            }
            unset($log);
            $query_logs_part= implode(',', $logs_part);
            $db->query($query_common_part . $query_logs_part);
        }
    }

    /**
     * Загрузка списка актов обмена или списка логов для акта обмена 
     * @param int|null $exchange_id
     * @return array
     */
    public function load($exchange_id = null) {
        $db = nc_db();

        if (empty($exchange_id)) {
            // Список логов для всех обменов
            $exchanges_ids = $db->get_col(
                "SELECT `Object_Exchange_ID`
                FROM `Netshop_ExchangeLog`
                WHERE `Object_ID`='{$this->object_id}'
                GROUP BY `Object_Exchange_ID`
                ORDER BY MIN(`Created`) DESC"
            );

            $exchanges = array_flip($exchanges_ids);

            foreach ($exchanges as $exchange_id=>&$data) {
                $data = $db->get_row(
                    "SELECT
                        MIN(`Created`) AS `Started`,
                        MAX(`Created`) AS `Finished`
                    FROM `Netshop_ExchangeLog`
                    WHERE `Object_Exchange_ID`='{$exchange_id}'",
                    ARRAY_A
                );
            }

            return $exchanges;
        } else {
            // Логи для конкретного обмена
            return (array)$db->get_results(
                "SELECT `Created`,`Message`,`Type`,`Action`,`File_Path`
                FROM `Netshop_ExchangeLog`
                WHERE `Object_Exchange_ID`='{$exchange_id}'
                ORDER BY `ExchangeLog_ID` ASC",
                ARRAY_A
            );
        }
    }

    /**
     * Подсчет статистики объекта лога
     * @param int $exchange_id
     * @return array
     */
    public function statistics($exchange_id) {
        $db = nc_db();
        $sql = "
            SELECT COUNT(`Action`) AS `v`, `Action` AS `k`
            FROM `Netshop_ExchangeLog`
            WHERE `Object_Exchange_ID`='{$exchange_id}' AND `Action`!=''
            GROUP BY `Action`
        ";
        $data = $db->get_results($sql, ARRAY_A);

        $result = array();
        if (!empty($data)) {
            foreach ($data as $row) {
                $result[$row['k']] = $row['v'];
            }
        }

        return $result;
    }

    /**
     * Удаление логов для текущего объекта
     */
    public function delete() {
        $db = nc_db();
        $db->query(
            "DELETE FROM `Netshop_ExchangeLog`
            WHERE `Object_ID`='{$this->object_id}'"
        );
    }

    /**
     * Построение отчета о определенном обмене
     * @param $exchange_id
     * @return string
     */
    public function build_report($exchange_id) {
        $nc_core = nc_core::get_object();
        $logs = $this->load($exchange_id);
        $statistics = $this->statistics($exchange_id);

        $view_path = $nc_core->MODULE_FOLDER . 'netshop/admin/views/exchange/object_report.view.php';
        $view = $nc_core->ui->view($view_path);
        $view->with('logs', $logs);
        $view->with('statistics', $statistics);

        return $view->make();
    }
}