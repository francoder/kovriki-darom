<?php

/**
 * Содержит общую для объектов обмена функциональность.
 */
abstract class nc_netshop_exchange_object extends nc_record {
    // Когда присылать отчет: всегда
    const EXCHANGE_REPORT_ALWAYS = 0;
    // Когда присылать отчет: при возникновении ошибок
    const EXCHANGE_REPORT_ON_ERROR = 1;
    // Когда присылать отчет: никогда
    const EXCHANGE_REPORT_NONE = 2;

    // Типы обмена
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    // Режим обмена
    const MODE_MANUAL = 'manual';
    const MODE_AUTOMATED = 'automated';

    // Фиктивное поле ("Не выбрано")
    const FAKE_FIELD_NO_FIELD = '__NO_FIELD__';
    // Фейковое поле ("Новое поле")
    const FAKE_FIELD_NEW_FIELD = '__NEW_FIELD__';
    // Фейковое поле ("Артикул родителя")
    const FAKE_FIELD_PARENT_FIELD = '__PARENT_FIELD__';

    protected $primary_key = 'exchange_id';
    protected $table_name = 'Netshop_Exchange';

    /* @var nc_netshop_exchange_handler $handler */
    public $handler = null;

    protected $properties = array(
        'exchange_id' => null,
        'catalogue_id' => null,
        'priority' => 0,
        'name' => NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_EXCHANGE_NAME,
        'type' => 'import',
        'format' => 'csv',
        'mode' => 'manual',
        'email' => '',
        'remote_file_url' => '',
        'report' => 0,
        'cron_key' => '',
        'checked' => true,
        'automated_mode_enabled' => false,
    );

    protected $serialized_properties = array(
        'exchange_mapping' => null,
    );

    protected $mapping = array(
        'exchange_id' => 'Exchange_ID',
        'catalogue_id' => 'Catalogue_ID',
        'priority' => 'Priority',
        'name' => 'Name',
        'type' => 'Type',
        'format' => 'Format',
        'mode' => 'Mode',
        'email' => 'Email',
        'remote_file_url' => 'RemoteFileUrl',
        'report' => 'Report',
        'exchange_mapping' => 'Exchange_Mapping',
        'cron_key' => 'CronKey',
        'checked' => 'Checked',
        'automated_mode_enabled' => 'AutomatedModeEnabled',
    );

    /**
     * Получение объекта обмена нужного подкласса по ID объекта
     * @param $id
     * @return static
     * @throws nc_netshop_exchange_exception
     */
    public static function by_id($id) {
        $db = nc_db();
        $id = (int)$id;
        $data = $db->get_row("SELECT * FROM `Netshop_Exchange` WHERE `Exchange_ID` = $id", ARRAY_A);

        if (empty($data)) {
            throw new nc_netshop_exchange_exception("Exchange object with ID={$id} not found");
        }

        // Тип и формат объекта обмена
        $type = nc_netshop_exchange_object::TYPE_IMPORT;
        $format = nc_netshop_exchange_import::FORMAT_CSV;

        if (!empty($data)) {
            $type = $data['Type'] ?: $type;
            $format = $data['Format'] ?: $format;
        }

        $object = static::get_by_format_and_type($type, $format);
        $object->set_values_from_database_result($data);
        return $object;
    }

    /**
     * Получение объекта обмена нужного подкласса с указанными свойствами
     * @param array $data
     * @return static
     */
    public static function from_array(array $data) {
        $type = nc_array_value($data, 'type');
        $format = nc_array_value($data, 'format');
        $object = static::get_by_format_and_type($type, $format);
        $object->set_values($data);
        return $object;
   }

    /**
     * Получение объекта обмена нужного подкласса
     * @param string $type
     * @param string $format
     * @return static
     * @throws nc_netshop_exchange_exception
     */
   protected static function get_by_format_and_type($type, $format) {
       $instantiated_class = "nc_netshop_exchange_{$type}_{$format}";
       if (!class_exists($instantiated_class)) {
           throw new nc_netshop_exchange_exception("Wrong type or format: class does not exist");
       }

       $called_class = get_called_class();
       if ($called_class !== $instantiated_class && !is_subclass_of($instantiated_class, $called_class)) {
           throw new nc_netshop_exchange_exception("Wrong type or format: class is not an instance of $called_class");
       }

       return new $instantiated_class;
   }


    public function __construct($values = null) {
        parent::__construct($values);
        $this->handler = new nc_netshop_exchange_handler($this);
    }

    /**
     * @return bool
     * @throws
     */
    public function is_checked() {
        return (bool)$this->get('checked');
    }

    /**
     * @return string
     * @throws
     */
    public function get_mode() {
        return $this->get('mode');
    }

    /**
     * @return bool
     * @throws
     */
    public function is_automated_mode_enabled() {
        return $this->is_checked() && (bool)$this->get('automated_mode_enabled');
    }

    /**
     * ID сайта, за которым закреплён объект обмена
     * @return integer
     * @throws
     */
    public function get_catalogue_id() {
        return $this->get('catalogue_id');
    }

    /**
     * Сохранение объекта обмена
     */
    public function save() {
        parent::save();

        // Создадим папку для файлов импорта, если всё ещё не создана
        $object_exchange_folder_path = $this->get_folder_path();

        if (!file_exists($object_exchange_folder_path)) {
            mkdir($object_exchange_folder_path, nc_core::get_object()->DIRCHMOD, true);
        }
    }

    /**
     * Удаление объекта обмена
     */
    public function delete() {
        // Удалим файлы обмена и папку
        $this->clean_folder();
        rmdir($this->get_folder_path());

        // Удалим логи
        $log = new nc_netshop_exchange_log($this);
        $log->delete();

        parent::delete();
    }

    /**
     * Получить путь к папке хранения файлов для обмена
     * @return string
     */
    public function get_folder_path() {
        $nc_core = nc_core::get_object();
        return $nc_core->FILES_FOLDER . "netshop/exchange/" . $this->get('exchange_id');
    }

    /**
     * Послать отчет о обмене на email
     * @param nc_netshop_exchange_log $log
     */
    protected function send_report(nc_netshop_exchange_log $log) {
        $exchange_id = $log->get_exchange_id();

        $nc_core = nc_core::get_object();

        // Отошлем Email c отчетом
        $email = $this->get('email');
        $report = $this->get('report');

        if ($email && $report != nc_netshop_exchange_object::EXCHANGE_REPORT_NONE) {
            $statistics = $log->statistics($exchange_id);

            // Есть ошибки?
            $has_errors = !empty($statistics[nc_netshop_exchange_log::ACTION_ERROR]) || !empty($statistics[nc_netshop_exchange_log::ACTION_CRITICAL_ERROR]);

            if ($report == nc_netshop_exchange_object::EXCHANGE_REPORT_ALWAYS || ($report == nc_netshop_exchange_object::EXCHANGE_REPORT_ON_ERROR && $has_errors)) {
                $mail_body = $log->build_report($exchange_id);

                // Отошлём отчет на почту
                $mailer = new nc_mail();
                $mailer->mailbody('', $mail_body);
                $mailer->send(
                    $email,
                    $nc_core->get_settings('SpamFromEmail'),
                    $nc_core->get_settings('SpamFromEmail'),
                    NETCAT_MODULE_NETSHOP_EXCHANGE_REPORT . ' #' . $exchange_id
                );

                $log->add(array(
                    'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_REPORT_HAS_BEEN_SENT,
                    'type' => nc_netshop_exchange_log::TYPE_INFO,
                    'action' => nc_netshop_exchange_log::ACTION_REPORT_HAS_BEEN_SENT,
                ));
            }
        }
    }

    /**
     * Получить пути к файлам в папке обмена
     * @return array
     */
    public function get_files_paths() {
        $files_paths = array();
        $folder = $this->get_folder_path();
        if (!file_exists($folder)) {
            return $files_paths;
        }
        $rdi = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
        if (iterator_count($rdi) === 0) {
            return $files_paths;
        }
        $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($rii as $file_info) {
            $files_paths[] = $file_info->getRealPath();
        }
        return $files_paths;
    }

    /**
     * Очистить папку для файлов обмена
     */
    public function clean_folder() {
        $files_paths = $this->get_files_paths();

        if (empty($files_paths)) {
            return;
        }

        // Сортировка по длине содержимого
        usort($files_paths, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($files_paths as $file_path) {
            $function = is_dir($file_path) ? 'rmdir' : 'unlink';
            $function($file_path);
        }
    }

    /**
     * Получение возможных предупреждений в зависимости от окружения и размеров файлов
     * @return array
     */
    public function get_environment_warnings() {
        return array();
    }

}
