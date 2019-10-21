<?php

abstract class nc_netshop_exchange_import extends nc_netshop_exchange_object {
    // Форматы импорта
    const FORMAT_CSV = 'csv';
    const FORMAT_XLS = 'xls';
    const FORMAT_YML = 'yml';
    const FORMAT_CML = 'cml';
    const FORMAT_PRICE = 'price';

    protected $property_field_title_prefixes = array();
    protected $hard_mapped_fields = array();
    /** @var nc_netshop_exchange_import_mapping  (не путать с $mapping из nc_record!) */
    private $import_mapping;

    /**
     * Возвращает расширения приемлемых файлов
     * @return array
     */
    public static function get_acceptable_files_extensions() {
        return array('zip');
    }

    public function __construct($values = null) {
        parent::__construct($values);

        $this->import_mapping = new nc_netshop_exchange_import_mapping($this);
        $this->property_field_title_prefixes = array();
    }

    /**
     * Загрузка файлов в папку для хранения файлов для обмена
     * @param array $files
     * @param bool $delete_old
     * @return bool status
     */
    public function upload_files(array $files = array(), $delete_old = true) {
        if (empty($files)) {
            return false;
        }

        $folder_path = $this->get_folder_path();

        if ($delete_old) {
            $this->clean_folder();
        }

        // Загрузим файлы по нужному адресу
        for ($i = 0; $i < count($files['name']); $i++) {
            $file_name = $files['name'][$i];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_path = $files['tmp_name'][$i];

            // Если не архив - переносим файлы в папку импорта
            if ($file_extension !== 'zip') {
                rename($file_path, $folder_path . '/' . $file_name);
            } else if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();

                if ($zip->open($file_path) === true) {
                    $zip->extractTo($folder_path . '/');
                    $zip->close();
                }
            }
        }

        // Удалим неразрешённые файлы
        $files_paths = $this->get_files_paths();
        foreach ($files_paths as $file_path) {
            if (is_dir($file_path)) {
                continue;
            }
            $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            // Не пропускаем архивы внутри архивов и исполняемые файлы
            $is_allowed_file_extension = !in_array($file_extension, array('zip', 'php'));
            // Не пропускаем мусор внутри архива, если архив был сделан из под MacOS
            $is_macos_specific = strpos($file_path, '__MACOSX') !== false;
            if ($is_allowed_file_extension && !$is_macos_specific) {
                continue;
            }
            unlink($file_path);
        }

        return true;
    }

    /**
     * Загрузка файлов по удалённому URL
     * @param $url
     * @param bool $delete_old
     * @return bool
     */
    public function upload_file_from_url($url, $delete_old = true) {
        if (empty($url)) {
            return false;
        }

        if (!strpos($url, '://')) {
            return false;
        }

        list($tmp_file_name) = explode('?', $url);
        $tmp_file_name = pathinfo($tmp_file_name, PATHINFO_BASENAME);
        $tmp_file_path = nc_Core::get_object()->TMP_FOLDER . $tmp_file_name;

        if (!copy($url, $tmp_file_path)) {
            return false;
        }

        $files = array(
            'name' => array($tmp_file_name),
            'tmp_name' => array($tmp_file_path),
        );

        return $this->upload_files($files, $delete_old);
    }

    /**
     * Получение возможных предупреждений в зависимости от окружения и размеров файлов
     * @return array
     */
    public function get_environment_warnings() {
        $warnings = array();
        $files_paths = $this->get_acceptable_files_paths();
        foreach ($files_paths as $file_path) {
            $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
            $file_name = pathinfo($file_path, PATHINFO_BASENAME);
            $file_size = filesize($file_path);
            $critical_file_size = $this->get_critical_file_size($file_extension);
            $critical_file_size = $this->adjust_critical_file_size_with_system_settings($critical_file_size);
            if ($file_size >= $critical_file_size) {
                $warnings[] = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_WARNING_LARGE_FILE_SIZE, $file_name);
            }
        }
        return $warnings;
    }

    /**
     * Получение критического размера файла в зависимости от расширения
     * @param string $extension - расширение файла
     * @return integer - критический размер файла в байтах
     */
    protected function get_critical_file_size($extension) {
        return nc_netshop_exchange_helper::mb_to_bytes(10);
    }


    /**
     * Изменение критического размера файла в зависимости от настроек системы
     * @param number $critical_file_size
     * @return number $critical_file_size
     */
    protected function adjust_critical_file_size_with_system_settings($critical_file_size) {
        // Масштабируем в зависимости от времени выполнения скрипта
        // (0 - неограниченно по времени выполнения; чем больше значение, тем бОльший по размеру файл сможем загрузить)
        $max_execution_time = (int)ini_get('max_execution_time');
        $critical_file_size *= nc_netshop_exchange_helper::get_multiplier_by_value($max_execution_time, array(
            0 => 10,
            1 => 0.1,
            10 => 0.5,
            30 => 1,
            60 => 1.5,
            120 => 3,
            300 => 5,
            600 => 10,
        ));
        // Масштабируем в зависимости от выделяемой памяти
        // (0 - не ограничено; чем больше значение, тем бОльший по размеру файл сможем загрузить)
        $memory_limit = nc_core::get_object()->get_memory_limit();
        $critical_file_size *= nc_netshop_exchange_helper::get_multiplier_by_value($memory_limit, array(
            0 => 10,
            1 => 0.05,
            nc_netshop_exchange_helper::mb_to_bytes(32) => 0.1,
            nc_netshop_exchange_helper::mb_to_bytes(128) => 0.3,
            nc_netshop_exchange_helper::mb_to_bytes(256) => 0.5,
            nc_netshop_exchange_helper::mb_to_bytes(512) => 0.8,
            nc_netshop_exchange_helper::mb_to_bytes(1024) => 1,
            nc_netshop_exchange_helper::mb_to_bytes(4096) => 4,
            nc_netshop_exchange_helper::mb_to_bytes(10240) => 10,
        ));
        return $critical_file_size;
    }

    /**
     * Возвращает пути к основным файлам в папке обмена
     * @return array
     */
    public function get_acceptable_files_paths() {
        $all_files_paths = $this->get_files_paths();

        if (empty($all_files_paths)) {
            return $all_files_paths;
        }

        $files_paths = array();

        $acceptable_files_extensions = static::get_acceptable_files_extensions();

        foreach ($all_files_paths as $file_path) {
            $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

            if (in_array($file_extension, $acceptable_files_extensions)) {
                $files_paths[] = $file_path;
            }
        }

        return $files_paths;
    }

    /**
     * Есть ли основные файлы для обмена?
     * @return bool
     */
    public function has_acceptable_files() {
        $acceptable_files_paths = $this->get_acceptable_files_paths();

        return count($acceptable_files_paths) > 0;
    }

    /**
     * Подготовить данные перед маппингом - парсинг данных, которые нужно разнести по компонентам и разделам.
     * Реализация в дочерних класса: csv, xls, и т.д.
     */
    public function prepare_items_for_mapping() {

    }

    /**
     * Помощник для маппинга
     * @return nc_netshop_exchange_import_mapping
     */
    public function get_mapping() {
        return $this->import_mapping;
    }

    /**
     * Преобразует item_key (уникальный идентификатор для доступа к конкретному куску данных) в человеческий вид
     * @param string $item_key
     * @return mixed
     */
    public function item_key_info($item_key) {
        $item_key_data = nc_netshop_exchange_helper::item_key_to_data($item_key);
        $item_key_data['file_name'] = pathinfo($item_key_data['file_path'], PATHINFO_BASENAME);
        return $item_key_data;
    }

    /**
     * Возвращает компоненты, которые участвуют в маппинге
     * @return array
     */
    public function get_components() {
        return nc_netshop_exchange_helper::get_goods_components_with_fields(null, true, true);
    }

    /**
     * Для некоторых типов обмена (yml, cml) можно сходу настроить маппинг для определенных полей
     * Реализация в дочерних класса: yml, cml, и т.д.
     * @return array
     */
    public function get_hard_mapped_fields() {
        return $this->hard_mapped_fields;
    }

    /**
     * Предугадывание полей на основании предыдущих шагов маппинга
     * @param $components
     * @param $mapping
     * @param null|array $captions
     */
    public function extend_hard_mapped_fields($components, $mapping, $captions = null) {
        if (empty($components)) {
            return;
        }
        // Поля, которые можно автоматически сопоставить на основании прошлых шагов маппинга
        $is_extended_format = in_array($this['format'], array(
            nc_netshop_exchange_import::FORMAT_YML,
            nc_netshop_exchange_import::FORMAT_CML,
        ));
        $hard_mapped_fields_from_mapping = array();
        if (!empty($mapping) && $is_extended_format) {
            foreach ($mapping as $item_key => $data) {
                foreach ($data['fields'] as $field_title => $field_id) {
                    if (array_key_exists($field_title, $this->hard_mapped_fields)) {
                        continue;
                    }
                    $hard_mapped_fields_from_mapping[$field_title] = $field_id;
                }
            }
            $fields_names = array();
            foreach ($components as $component_id => $component_data) {
                foreach ($component_data['fields'] as $field_id => $field_data) {
                    $field_name = $field_data['name'];
                    if ($field_id <= 0) {
                        continue;
                    }
                    $fields_names[$field_id] = $field_name;
                }
            }
            $fields_names[-2] = nc_netshop_exchange_object::FAKE_FIELD_PARENT_FIELD;
            foreach ($hard_mapped_fields_from_mapping as $field_title => &$field_name) {
                $field_name = $fields_names[$field_name];
            }
            unset($field_name);
        }

        // Поля, которые можно автоматически сопоставить на основании прошлых шагов маппинга
        $hard_mapped_fields_from_captions = array();
        if (!empty($captions)) {
            foreach ($captions as $i => $caption) {
                foreach ($components as $component_id => $component_data) {
                    foreach ($component_data['fields'] as $field_id => $field_data) {
                        $field_name = $field_data['name'];
                        $field_description = $field_data['description'];
                        if ($caption != $field_description) {
                            continue;
                        }
                        $hard_mapped_fields_from_captions[$i] = $field_name;
                    }
                }
            }
        }

        // Скомбинируем полученные поля
        $this->hard_mapped_fields = $this->hard_mapped_fields + $hard_mapped_fields_from_mapping + $hard_mapped_fields_from_captions;
    }

    /**
     * Для некоторых типов обмена (yml, cml) можно автоматически создать новые поля.
     * Для этого нужно проанализировать заголовок поля.
     * @param string $field_title
     * @return boolean
     */
    public function can_create_new_field($field_title) {
        if (empty($this->property_field_title_prefixes)) {
            return false;
        }
        foreach ($this->property_field_title_prefixes as $prefix) {
            if (substr($field_title, 0, strlen($prefix)) == $prefix) {
                return true;
            }
        }
        return false;
    }

    /**
     * Генерация данных для нового поля по его заголовку
     * Реализация в дочерних класса: yml, cml, и т.д.
     * @param string $field_title
     * @return array
     */
    public function new_field_data_from_field_title($field_title) {
        $description = str_replace($this->property_field_title_prefixes, '', $field_title);
        $name = ucfirst(strtolower(nc_transliterate($description)));
        $name = str_replace(array(' ', '-'), '_', $name);
        $name = preg_replace('/([^a-zA-Z0-9_])+/', '', $name);
        $name = trim($name, '_');

        return array(
            'name' => 'Property_' . $name,
            'description' => $description,
        );
    }

    /**
     * Уникальное поле, которое используется при обмене для сопоставления товаров в выгрузке товарам на сайте
     * @return string
     */
    public function get_main_field_name() {
        return 'Article';
    }

    /**
     * Описание уникального поля
     * @return string
     */
    public function get_main_field_description() {
        return NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_NAME_ARTICLE;
    }

    /**
     * У файлов импорта для данного формата всегда есть заголовок?
     * @return string
     */
    public function always_has_caption() {
        return false;
    }

    /**
     * Возвращает массив данных из файла для обмена или настройки маппинга.
     * Реализация в дочерних класса: csv, xls, и т.д.
     * @param string $file_path
     * @param null $offset
     * @return array
     */
    public function get_data($file_path, $offset = null) {
        return array();
    }

    /**
     * Возвращает ключи (или индексы) для полей файла.
     * Реализация в дочерних класса: csv, xls, и т.д.
     * @param array $file_data
     * @return array
     */
    public function get_data_fields_keys($file_data) {
        return array();
    }

    /**
     * Стандартизирование валюты
     * @param int $site_id
     * @param string $currency
     * @return string mixed
     */
    protected function convert_currency($site_id, $currency) {
        static $rouble_codes = array();
        if (!isset($rouble_codes[$site_id])) {
            $rouble_codes[$site_id] = $this->get_rouble_code($site_id);
        }

        $rouble_code = $rouble_codes[$site_id];

        $current = $currency;
        $currency = nc_strtolower($currency);
        $replacements = array(
            'rur' => $rouble_code,
            'rub' => $rouble_code,
            'руб' => $rouble_code,
        );
        return !isset($replacements[$currency]) ? $current : $replacements[$currency];
    }

    /**
     * Возвращает код рубля (RUB или RUR), используемый на сайте
     * @param int $site_id
     * @return string
     */
    protected function get_rouble_code($site_id) {
        $shop_currencies = nc_netshop::get_instance($site_id)->get_setting('Currencies');
        return in_array('RUB', $shop_currencies) ? 'RUB' : 'RUR';
    }

    /**
     * Стандартизирование единицы измерения
     * @param string $units
     * @return string
     */
    protected function convert_units($units) {
        $current = $units;
        $units = nc_strtolower($units);
        $replacements = array(
            'шт' => 'шт',
            'шт.' => 'шт',
            'pce' => 'шт',
        );
        return !isset($replacements[$units]) ? $current : $replacements[$units];
    }

    /**
     * Запустить обмен.
     */
    public function run() {
        @set_time_limit(0);

        $log = new nc_netshop_exchange_log($this);
        $log->add(array(
            'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_EXCHANGE_START,
        ));

        // Попробуем загрузить файлы если указан Url, и нет файлов для обмена
        if (!$this->has_acceptable_files() && $this->get('remote_file_url')) {
            $log->add(array(
                'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_TRYING_TO_LOAD_FILES_BY_URL,
                'type' => nc_netshop_exchange_log::TYPE_INFO,
            ));

            $uploaded = $this->upload_file_from_url($this->get('remote_file_url'), true);
            if ($uploaded) {
                $log->add(array(
                    'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_FILES_UPLOADED,
                    'type' => nc_netshop_exchange_log::TYPE_SUCCESS,
                ));
            } else {
                $log->add(array(
                    'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_FAILED_TO_LOAD_FILES_BY_URL,
                    'type' => nc_netshop_exchange_log::TYPE_DANGER,
                    'action' => nc_netshop_exchange_log::ACTION_CRITICAL_ERROR,
                ));
            }
        }

        $all_files_paths = $this->get_files_paths();

        // Если есть нужные файлы
        if ($this->has_acceptable_files()) {
            // Получим настройки обмена
            $mappings = json_decode($this->get('exchange_mapping'), true);
            if (empty($mappings)) {
                $log->add(array(
                    'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_NOT_CONFIGURED,
                    'type' => nc_netshop_exchange_log::TYPE_DANGER,
                    'action' => nc_netshop_exchange_log::ACTION_CRITICAL_ERROR,
                ));
            } else {
                $items = array_keys($mappings);
                // Импортируем каждый объект импорта
                foreach ($items as $item) {
                    $mapping = $mappings[$item];

                    $this->run_for_item($item, $mapping, $log);
                }
            }
        }
        // Если нужных файлов нет, но вообще файлы в папке обмена есть
        else if (count($all_files_paths) > 0) {
            $log->add(array(
                'message' => sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_FILES_NOT_FOUND, static::get_acceptable_files_extensions()),
                'type' => nc_netshop_exchange_log::TYPE_DANGER,
                'action' => nc_netshop_exchange_log::ACTION_CRITICAL_ERROR,
            ));
        }
        // Если файлов вообще нет
        else {
            $log->add(array(
                'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_EXCHANGE_FOLDER_IS_EMPTY,
                'type' => nc_netshop_exchange_log::TYPE_DANGER,
                'action' => nc_netshop_exchange_log::ACTION_ERROR,
            ));
        }

        $log->add(array(
            'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_EXCHANGE_FINISH,
        ));
        $log->save();
        $this->send_report($log);
        $this->clean_folder();
        return $log->get_exchange_id();
    }

    /**
     * Проверка доступности файла закрепленного за определённым куском данных
     * (Данный код переопределяется в import_cml классе)
     * @param string $file_path
     * @return boolean
     */
    protected function check_item_file_path($file_path) {
        return file_exists($file_path);
    }

    /**
     * Изменение заданного пути до файла обмена
     * (Данный код переопределяется в import_cml классе)
     * @param string $file_path
     * @return boolean
     */
    protected function adjust_item_file_path($file_path) {
        return $file_path;
    }

    /**
     * Запуск обмена для определенного "куска" данных импорта
     * @param string $item
     * @param array $mapping
     * @param nc_netshop_exchange_log $log
     */
    protected function run_for_item($item, array $mapping, nc_netshop_exchange_log $log) {
        $nc_core = nc_Core::get_object();
        $db = nc_db();

        $item_key_data = nc_netshop_exchange_helper::item_key_to_data($item);
        $file_path = $item_key_data['file_path'];
        $scope = $item_key_data['scope'];
        $scope_name = $item_key_data['scope_name'];

        if (!$this->check_item_file_path($file_path)) {
            $log->add(array(
                'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_FILE_NOT_FOUND,
                'file_path' => $file_path,
                'type' => nc_netshop_exchange_log::TYPE_DANGER,
                'action' => nc_netshop_exchange_log::ACTION_ERROR,
            ));

            return;
        }

        $file_path = $this->adjust_item_file_path($file_path);

        $log->add(array(
            'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_START_FILE_PROCESSING,
            'file_path' => $file_path . '|' . $scope_name,
        ));

        $object_exchange_folder_path = $this->get_folder_path();

        $file_data = $this->get_data($file_path, $scope);

        if (empty($file_data)) {
            $log->add(array(
                'message' =>  NETCAT_MODULE_NETSHOP_EXCHANGE_FILE_IS_EMPTY,
                'file_path' => $file_path . '|' . $scope_name,
                'type' => nc_netshop_exchange_log::TYPE_DANGER,
            ));
        } else {
            $file_data = $file_data['goods'];
            $fields_keys = $this->get_data_fields_keys($file_data);
            $component_id = $mapping['component_id'];
            $subdivision_id = $mapping['subdivision_id'];
            try {
                $site_id = $nc_core->subdivision->get_by_id($subdivision_id, 'Catalogue_ID');
            } catch (Exception $e) {
                $site_id = $nc_core->catalogue->id();
            }

            if (empty($file_data) || empty($component_id)) {
                $log->add(array(
                    'message' =>  NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_NO_GOOD_DATA_OR_COMPONENT_NOT_MAPPED,
                    'file_path' => $file_path . '|' . $scope_name,
                    'type' => nc_netshop_exchange_log::TYPE_WARNING,
                ));
            } else {
                $fields = $mapping['fields'];
                $skip_rows = $mapping['skip_rows'];
                $index_field = $this->get_main_field_name();
                $sub_class_id = $db->get_var("SELECT `Sub_Class_ID` FROM `Sub_Class` WHERE `Subdivision_ID`='{$subdivision_id}' AND `Class_ID`='{$component_id}'");

                $component_fields = nc_netshop_exchange_helper::get_goods_component_with_fields($component_id);
                $component_fields = $component_fields['fields'];

                $priority = (int)$db->get_var("SELECT `Priority` FROM `Message{$component_id}` ORDER BY `Priority` DESC LIMIT 1") + 1;

                // Удалим заголовок если есть
                if ($skip_rows) {
                    $file_data = array_slice($file_data, $skip_rows);
                }

                // Обработаем файл
                foreach ($file_data as $row) {
                    // Поля и файлы для загрузки в поля типа "Файл"
                    $tmp_fields_files = array();

                    // Поля и файлы для загрузки в поля типа "Мультифайл"
                    $tmp_fields_multifiles = array();

                    // Получим данные о полях в массив
                    $file_fields = array();

                    foreach ($row as $index => $col) {
                        // Ключ поля (для XLS и CSV - index поля, для YML - ключевое слово, напр. "name" или "vendor")
                        $field_key = $fields_keys[$index];

                        // Id поля в компоненте через ключевое слово поля
                        $field_id = !empty($fields[$field_key]) ? $fields[$field_key] : 0;

                        // Для поля не настроено соответствие
                        $field_mapped = $field_id > 0 || $field_id == -2;
                        if (!$field_mapped) {
                            continue;
                        }

                        $field_data = $component_fields[$field_id];

                        // Название поля (Name, Description, и т.д.)
                        $field_name = $field_data['name'];
                        // Тип поля
                        $field_type = $field_data['type_of_data_id'];
                        // Формат поля
                        $field_format = $field_data['format'];
                        // Значение поля
                        $field_value = $col;

                        // Может понадобиться игнорировать поле, если есть необходимость сохранить старое значение
                        // (например, нельзя удалять значение у поля "Файл", т.к. мы не удалим старый файл)
                        $ignore_field = false;

                        // Поля, которые необходимо обрабатывать индивидуально
                        switch ($field_name) {
                            // Признак подчиненного товара
                            case nc_netshop_exchange_object::FAKE_FIELD_PARENT_FIELD:
                                $ignore_field = true;

                                // Не указан артикул родителя?
                                if (empty($field_value)) {
                                    break;
                                }

                                // Найдем Id родителя
                                $parent_message_id = $db->get_var("SELECT `Message_ID` FROM `Message{$component_id}` WHERE `{$index_field}`='{$field_value}'");

                                // Родитель не найден?
                                if (empty($parent_message_id)) {
                                    break;
                                }

                                // Добавим признак подчиненного товара
                                $file_fields['Parent_Message_ID'] = $parent_message_id;

                                break;

                            // Все остальные поля
                            default:
                                // В зависимости от типов полей
                                switch ($field_type) {
                                    // Строка
                                    case NC_FIELDTYPE_STRING:
                                        $field_value = (string)$field_value;
                                        break;

                                    // Целое число
                                    case NC_FIELDTYPE_INT:
                                        $field_value = $this->convert_fieldtype_int($field_value, $field_name, $file_path, $scope_name, $log);
                                        break;

                                    // Текстовый блок
                                    case NC_FIELDTYPE_TEXT:
                                        $field_value = (string)$field_value;
                                        break;

                                    // Список
                                    case NC_FIELDTYPE_SELECT:
                                        if (empty($field_value)) {
                                            break;
                                        }

                                        // Получим из формата поля связанный с полем список
                                        list($list_name) = explode(':', $field_format);

                                        // Обработка значения для специальных полей типа "Список"
                                        switch ($list_name) {
                                            case 'ShopCurrency':
                                                $field_value = $this->convert_currency($site_id, $field_value);
                                                break;

                                            case 'ShopUnits':
                                                $field_value = $this->convert_units($field_value);
                                                break;
                                        }

                                        // Id значения
                                        $field_value_old = $field_value;
                                        $object_created = false;
                                        $field_value = nc_netshop_exchange_helper::list_object_id_from_value($list_name, $field_value, $object_created);

                                        if ($field_value && $object_created) {
                                            $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_ADDED_TO_LIST, $field_name, $list_name, $field_value_old);
                                            $log->add(array(
                                                'message' => $msg,
                                                'file_path' => $file_path . '|' . $scope_name,
                                                'type' => nc_netshop_exchange_log::TYPE_WARNING,
                                                'action' => nc_netshop_exchange_log::ACTION_LIST_INSERTION,
                                            ));
                                        }

                                        break;

                                    // Логическая переменная
                                    case NC_FIELDTYPE_BOOLEAN:
                                        // Старое значение
                                        $field_value_old = $field_value;

                                        // Новое значение
                                        $field_value = nc_strtolower(trim($field_value));
                                        $true_values = array('1', 'да', 'yes', 'true', 'ok', '+');
                                        $field_value = in_array($field_value, $true_values) ? '1' : '0';

                                        // Залоггируем, если преобразовали
                                        if ($field_value != $field_value_old) {
                                            $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_TYPE_CASTING_FOR_FIELD, $field_name, $field_value_old, $field_value);
                                            $log->add(array(
                                                'message' => $msg,
                                                'file_path' => $file_path . '|' . $scope_name,
                                                'type' => nc_netshop_exchange_log::TYPE_WARNING,
                                                'action' => nc_netshop_exchange_log::ACTION_TYPE_CONVERSION,
                                            ));
                                        }

                                        break;

                                    // Файл
                                    case NC_FIELDTYPE_FILE:
                                        if (empty($field_value)) {
                                            break;
                                        }

                                        $file_to_field_path = null;

                                        // Если есть '://' - то это внешний файл, который необходимо скачать и сохранить
                                        if (strpos($field_value, '://')) {
                                            $file_to_field_path = $nc_core->TMP_FOLDER . pathinfo($field_value, PATHINFO_BASENAME);
                                            list($file_to_field_path) = explode('?', $file_to_field_path);

                                            copy($field_value, $file_to_field_path);
                                        } else {
                                            $file_to_field_path = $object_exchange_folder_path . '/' . $field_value;
                                        }

                                        $file_to_field_path = nc_standardize_path_to_file($file_to_field_path);

                                        // Если файл найден - добавим его для добавления в компонент
                                        if (file_exists($file_to_field_path) && !is_dir($file_to_field_path)) {
                                            $tmp_fields_files[] = array(
                                                'field' => $field_name,
                                                'file_path' => $file_to_field_path,
                                            );
                                        } else {
                                            $file_http_path = str_replace($nc_core->DOCUMENT_ROOT, '', $file_to_field_path);
                                            $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_FILE_HAS_NOT_BEEN_FOUND, $field_name, $file_http_path);
                                            $log->add(array(
                                                'message' => $msg,
                                                'file_path' => $file_path . '|' . $scope_name,
                                                'type' => nc_netshop_exchange_log::TYPE_WARNING,
                                                'action' => nc_netshop_exchange_log::ACTION_FILE_NOT_FOUND,
                                            ));
                                        }

                                        $ignore_field = true;

                                        break;

                                    // Число с плавающей запятой
                                    case NC_FIELDTYPE_FLOAT:
                                        $field_value = $this->convert_fieldtype_float($field_value, $field_name, $file_path, $scope_name, $log);
                                        break;

                                    // Дата и время
                                    case NC_FIELDTYPE_DATETIME:
                                        $field_value = date('Y-m-d H:i:s', strtotime($field_value));
                                        break;

                                    // Множественный список
                                    case NC_FIELDTYPE_MULTISELECT:
                                        if (empty($field_value)) {
                                            break;
                                        }

                                        // Получим из формата поля связанный с полем список
                                        list($list_name) = explode(':', $field_format);

                                        // Значения множественного списка
                                        $field_value = explode('|', $field_value);
                                        foreach ($field_value as &$value) {
                                            // Id значения
                                            $value_old = $value;
                                            $object_created = false;
                                            $value = nc_netshop_exchange_helper::list_object_id_from_value($list_name, $value, $object_created);

                                            if ($value && $object_created) {
                                                $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_ADDED_TO_LIST, $field_name, $list_name, $value_old);
                                                $log->add(array(
                                                    'message' => $msg,
                                                    'file_path' => $file_path . '|' . $scope_name,
                                                    'type' => nc_netshop_exchange_log::TYPE_WARNING,
                                                    'action' => nc_netshop_exchange_log::ACTION_LIST_INSERTION,
                                                ));
                                            }
                                        }
                                        unset($value);

                                        $field_value = ',' . implode(',', $field_value) . ',';

                                        break;

                                    // Мультифайл
                                    case NC_FIELDTYPE_MULTIFILE:
                                        if (empty($field_value)) {
                                            break;
                                        }

                                        // Определим файлы
                                        $multifiles = explode('|', $field_value);
                                        $multifiles_count = count($multifiles);

                                        // В поле "Мультифайл" значение не должно быть
                                        $field_value = '';

                                        for ($i = 0; $i < $multifiles_count; $i++) {
                                            if (empty($multifiles[$i])) {
                                                $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_FILE_PATH_HAS_NOT_BEEN_FOUND, $field_name);
                                                $log->add(array(
                                                    'message' => $msg,
                                                    'file_path' => $file_path . '|' . $scope_name,
                                                    'type' => nc_netshop_exchange_log::TYPE_WARNING,
                                                    'action' => nc_netshop_exchange_log::ACTION_FILE_NOT_FOUND,
                                                ));

                                                continue;
                                            }

                                            // Если есть '://' - то это внешний файл, который необходимо скачать и сохранить
                                            if (strpos($multifiles[$i], '://')) {
                                                // Откуда скачаем
                                                $src_file_url = $multifiles[$i];
                                                // Куда закачаем
                                                $multifiles[$i] = $nc_core->TMP_FOLDER . pathinfo($multifiles[$i], PATHINFO_BASENAME);
                                                list($multifiles[$i]) = explode('?', $multifiles[$i]);

                                                copy($src_file_url, $multifiles[$i]);
                                            } else {
                                                $multifiles[$i] = $object_exchange_folder_path . '/' . $multifiles[$i];
                                            }

                                            $multifiles[$i] = nc_standardize_path_to_file($multifiles[$i]);

                                            // Продолжим цикл, если файл найден
                                            if (file_exists($multifiles[$i]) && !is_dir($multifiles[$i])) {
                                                continue;
                                            }

                                            $file_http_path = str_replace($nc_core->DOCUMENT_ROOT, '', $multifiles[$i]);
                                            $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_FILE_HAS_NOT_BEEN_FOUND, $field_name, $file_http_path);
                                            $log->add(array(
                                                'message' => $msg,
                                                'file_path' => $file_path . '|' . $scope_name,
                                                'type' => nc_netshop_exchange_log::TYPE_WARNING,
                                                'action' => nc_netshop_exchange_log::ACTION_FILE_NOT_FOUND,
                                            ));

                                            // Указанный файл не найден
                                            unset($multifiles[$i]);
                                        }

                                        // Если не найден ни один из указанных файлов
                                        if (count($multifiles) == 0) {
                                            break;
                                        }

                                        // Добавим файлы для обработки
                                        $multifiles = array_values($multifiles);

                                        $tmp_fields_multifiles[] = array(
                                            'field' => $field_name,
                                            'files_paths' => $multifiles,
                                        );

                                        break;
                                }

                                break;
                        }

                        if (!$ignore_field) {
                            $file_fields[$field_name] = $field_value;
                        }
                    }

                    // Артикул товара
                    $index_field_value = !empty($file_fields[$index_field]) ? $db->escape($file_fields[$index_field]) : null;
                    if (empty($index_field_value)) {
                        continue;
                    }

                    // Обновим или создадим запись
                    $message_id = $db->get_var("SELECT `Message_ID` FROM `Message{$component_id}` WHERE `{$index_field}`='{$index_field_value}'");

                    // Запрос обновления полей в компоненте
                    $query_set = array();
                    foreach ($file_fields as $field_name => $field_value) {
                        $field_value = $db->escape($field_value);
                        $query_set[] = " `{$field_name}`='{$field_value}' ";
                    }
                    $date = date('Y-m-d H:i:s');
                    if (empty($message_id)) {
                        $user_id = 1;
                        $query_set[] = " `Priority`='{$priority}' ";
                        $query_set[] = " `User_ID`='{$user_id}' ";
                        $query_set[] = " `LastUser_ID`='{$user_id}' ";
                        $query_set[] = " `Created`='{$date}' ";
                        $query_set[] = " `Subdivision_ID`='{$subdivision_id}' ";
                        $query_set[] = " `Sub_Class_ID`='{$sub_class_id}' ";
                        $query_set[] = " `Checked`='1' ";
                        $priority++;
                    }
                    $query_set[] = " `LastUpdated`='{$date}' ";
                    $query_set = implode(',', $query_set);

                    if (!empty($message_id)) {
                        $db->query(
                            "UPDATE `Message{$component_id}`
                            SET {$query_set}
                            WHERE `Message_ID`='{$message_id}'"
                        );

                        if ($db->rows_affected) {
                            $log->add(array(
                                'message' => sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_HAS_BEEN_UPDATED, $message_id, $index_field, $index_field_value),
                                'file_path' => $file_path . '|' . $scope_name,
                                'type' => nc_netshop_exchange_log::TYPE_SUCCESS,
                                'action' => nc_netshop_exchange_log::ACTION_OBJECT_UPDATED,
                            ));

                            $nc_core->event->execute(nc_Event::AFTER_OBJECT_UPDATED, $site_id, $subdivision_id, $sub_class_id, $component_id, $message_id);
                        } else {
                            $log->add(array(
                                'message' => sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_HAS_NOT_BEEN_UPDATED, $message_id, $index_field, $index_field_value),
                                'file_path' => $file_path . '|' . $scope_name,
                                'type' => nc_netshop_exchange_log::TYPE_DANGER,
                                'action' => nc_netshop_exchange_log::ACTION_OBJECT_NOT_UPDATED,
                            ));
                        }
                    } else {
                        $db->query(
                            "INSERT INTO `Message{$component_id}`
                            SET {$query_set}"
                        );

                        $message_id = $db->insert_id;

                        if (!empty($message_id)) {
                            $log->add(array(
                                'message' => sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_HAS_BEEN_ADDED, $message_id, $index_field, $index_field_value),
                                'file_path' => $file_path . '|' . $scope_name,
                                'type' => nc_netshop_exchange_log::TYPE_SUCCESS,
                                'action' => nc_netshop_exchange_log::ACTION_OBJECT_ADDED,
                            ));

                            $nc_core->event->execute(nc_Event::AFTER_OBJECT_CREATED, $site_id, $subdivision_id, $sub_class_id, $component_id, $message_id);
                        } else {
                            $log->add(array(
                                'message' => sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_HAS_NOT_BEEN_ADDED, $index_field, $index_field_value),
                                'file_path' => $file_path . '|' . $scope_name,
                                'type' => nc_netshop_exchange_log::TYPE_DANGER,
                                'action' => nc_netshop_exchange_log::ACTION_OBJECT_NOT_ADDED,
                            ));
                        }
                    }

                    // Обновим файлы в компоненте
                    if (!empty($message_id)) {
                        // Поля "Файл"
                        if (!empty($tmp_fields_files)) {
                            foreach ($tmp_fields_files as $tmp_field_files) {
                                $file_absolute_path = $tmp_field_files['file_path'];
                                $file_relative_path = str_replace($nc_core->DOCUMENT_ROOT, '', $file_absolute_path);
                                $nc_core->files->field_save_file($component_id, $tmp_field_files['field'], $message_id, $file_relative_path);
                                unlink($file_absolute_path);
                            }
                        }

                        // Поля "Мультифайл"
                        if (!empty($tmp_fields_multifiles)) {
                            foreach ($tmp_fields_multifiles as $tmp_field_multifiles) {
                                nc_netshop_exchange_helper::set_files($component_id, $message_id, $tmp_field_multifiles['field'], $tmp_field_multifiles['files_paths']);
                            }
                        }
                    }
                }
            }
        }

        $log->add(array(
            'message' => NETCAT_MODULE_NETSHOP_EXCHANGE_FINISH_FILE_PROCESSING,
            'file_path' => $file_path . '|' . $scope_name,
        ));
    }

    /**
     * Преобразование поля к integer
     * @param $value
     * @param $field_name
     * @param $file_path
     * @param $scope_name
     * @param nc_netshop_exchange_log $log
     * @return int
     */
    protected function convert_fieldtype_int($value, $field_name, $file_path, $scope_name, nc_netshop_exchange_log $log) {
        // Старое значение
        $value_old = $value;

        // Новое значение
        $value = str_replace(array(' ', ','), array('', '.'), $value);
        if (substr_count($value, '.')) {
            list($value) = explode('.', $value);
        }
        $value = (int)$value;

        // Залоггируем, если преобразовали
        if ($value != $value_old) {
            $log->add(array(
                'message' => sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_TYPE_CASTING_FOR_FIELD, $field_name, $value_old, $value),
                'file_path' => $file_path . '|' . $scope_name,
                'type' => nc_netshop_exchange_log::TYPE_WARNING,
                'action' => nc_netshop_exchange_log::ACTION_TYPE_CONVERSION,
            ));
        }

        return $value;
    }

    /**
     * Преобразование поля к float
     * @param $value
     * @param $field_name
     * @param $file_path
     * @param $scope_name
     * @param nc_netshop_exchange_log $log
     * @return string
     */
    protected function convert_fieldtype_float($value, $field_name, $file_path, $scope_name, nc_netshop_exchange_log $log) {
        // Старое значение
        $value_old = $value;

        // Новое значение
        $value = str_replace(array(' ', ','), array('', '.'), $value);
        $value = (float)$value;
        $value = str_replace(',', '.', $value);

        // Залоггируем, если преобразовали
        if ($value != $value_old) {
            $msg = sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_TYPE_CASTING_FOR_FIELD, $field_name, $value_old, $value);
            $log->add(array(
                'message' => $msg,
                'file_path' => $file_path . '|' . $scope_name,
                'type' => nc_netshop_exchange_log::TYPE_WARNING,
                'action' => nc_netshop_exchange_log::ACTION_TYPE_CONVERSION,
            ));
        }

        return $value;
    }

    /**
     * Обработка категорий (возвращает только те, в которых есть товары, и их родителей до корня)
     *
     * @param array $leaves
     * @param array $tree
     * @return array
     */
    protected function process_categories($leaves, $tree) {
        // Соберём все нужные разделы (в которых есть товары + их родители вплоть до корня)
        $necessary_nodes = $leaves;
        foreach ($leaves as $leaf) {
            do {
                $leaf = $tree[$leaf]['parent_id'];
                if (empty($leaf)) {
                    break;
                }
                if (in_array($leaf, $necessary_nodes)) {
                    break;
                }
                $necessary_nodes[] = $leaf;
            } while (true);
        }
        // Возвращаем только нужные разделы
        return array_intersect(array_keys($tree), $necessary_nodes);
    }
}