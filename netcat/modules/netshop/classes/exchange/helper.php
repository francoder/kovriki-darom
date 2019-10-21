<?php

/**
 * nc_netshop_exchange_helper
 * Вспомогательные функции.
 */
class nc_netshop_exchange_helper {
    /**
     * Получение Id объекта списка по его значению.
     * При отсутствии значения - произведет добавление в список.
     * Функция также кэширует запросы к спискам и их значениям.
     * @param $list_name
     * @param $value
     * @param $object_created - Флаг того, что объект в списке был создан
     * @return mixed null|int
     */
    public static function list_object_id_from_value($list_name, $value, &$object_created = false) {
        if (empty($value)) {
            return null;
        }

        // Черный список списков (нет таких списков).
        static $blacklist = array();
        // Кэш данных списков
        static $cache = array();

        $db = nc_db();

        // Нет такого списка
        if (in_array($list_name, $blacklist)) {
            return null;
        }

        // Загрузим список, если ещё не был загружен
        if (!array_key_exists($list_name, $cache)) {
            // Проверим, есть ли список вообще?
            $list_id = $db->get_var("SELECT `Classificator_ID` FROM `Classificator` WHERE `Table_Name`='{$list_name}'");
            if (empty($list_id)) {
                $blacklist[] = $list_name;
                return null;
            }

            $cache[$list_name] = array();

            // Список есть, загрузим объекты
            $objects = (array)$db->get_results(
                "SELECT `{$list_name}_ID` AS `id`,`{$list_name}_Name` AS `value`,`{$list_name}_Priority` AS `priority`
                FROM `Classificator_{$list_name}`
                ORDER BY `{$list_name}_Priority` ASC",
                ARRAY_A
            );

            foreach ($objects as $object) {
                $cache[$list_name][$object['value']] = array(
                    'id' => $object['id'],
                    'priority' => $object['priority'],
                );
            }
        }

        // Проверим, вдруг нет значения в списке
        if (!array_key_exists($value, $cache[$list_name])) {
            // Приоритет объекта
            $priority = 0;
            if (count($cache[$list_name])) {
                $last_object = current(array_slice($cache[$list_name], -1));
                $priority = $last_object['priority'] + 1;
            }

            // Добавим значение в список и в базу
            $db->query(
                "INSERT INTO `Classificator_{$list_name}`
                SET `{$list_name}_Name`='{$value}',`{$list_name}_Priority`='{$priority}',`Value`='',`Checked`='1'"
            );

            $cache[$list_name][$value] = array(
                'id' => $db->insert_id,
                'priority' => $priority,
            );

            $object_created = true;
        }

        return !empty($cache[$list_name][$value]['id']) ? $cache[$list_name][$value]['id'] : null;
    }

    /**
     * @return array
     */
    public static function get_formats() {
        return array(
            nc_netshop_exchange_import::FORMAT_CSV   => nc_netshop_exchange_helper::exchange_format_to_name(nc_netshop_exchange_import::FORMAT_CSV),
            nc_netshop_exchange_import::FORMAT_XLS   => nc_netshop_exchange_helper::exchange_format_to_name(nc_netshop_exchange_import::FORMAT_XLS),
            nc_netshop_exchange_import::FORMAT_YML   => nc_netshop_exchange_helper::exchange_format_to_name(nc_netshop_exchange_import::FORMAT_YML),
            nc_netshop_exchange_import::FORMAT_CML   => nc_netshop_exchange_helper::exchange_format_to_name(nc_netshop_exchange_import::FORMAT_CML),
            nc_netshop_exchange_import::FORMAT_PRICE => nc_netshop_exchange_helper::exchange_format_to_name(nc_netshop_exchange_import::FORMAT_PRICE),
        );
    }

    /**
     * @return array
     */
    public static function get_modes() {
        return array(
            nc_netshop_exchange_object::MODE_MANUAL    => nc_netshop_exchange_helper::exchange_mode_to_name(nc_netshop_exchange_object::MODE_MANUAL),
            nc_netshop_exchange_object::MODE_AUTOMATED => nc_netshop_exchange_helper::exchange_mode_to_name(nc_netshop_exchange_object::MODE_AUTOMATED),
        );
    }

    /**
     * @return array
     */
    public static function get_types() {
        return array(
            nc_netshop_exchange_object::TYPE_IMPORT => nc_netshop_exchange_helper::exchange_type_to_name(nc_netshop_exchange_object::TYPE_IMPORT),
            nc_netshop_exchange_object::TYPE_EXPORT => nc_netshop_exchange_helper::exchange_type_to_name(nc_netshop_exchange_object::TYPE_EXPORT),
        );
    }

    /**
     * Получение списка типов полей компонентов (кроме некоторых специфических)
     * @return array
     */
    public static function get_fields_types() {
        return array(
            NC_FIELDTYPE_STRING => NC_FIELDTYPE_STRING . ': ' . CLASSIFICATOR_TYPEOFDATA_STRING,
            NC_FIELDTYPE_INT => NC_FIELDTYPE_INT . ': ' . CLASSIFICATOR_TYPEOFDATA_INTEGER,
            NC_FIELDTYPE_TEXT => NC_FIELDTYPE_TEXT . ': ' . CLASSIFICATOR_TYPEOFDATA_TEXTBOX,
            NC_FIELDTYPE_SELECT => NC_FIELDTYPE_SELECT . ': ' . CLASSIFICATOR_TYPEOFDATA_LIST,
            NC_FIELDTYPE_BOOLEAN => NC_FIELDTYPE_BOOLEAN . ': ' . CLASSIFICATOR_TYPEOFDATA_BOOLEAN,
            NC_FIELDTYPE_FILE => NC_FIELDTYPE_FILE . ': ' . CLASSIFICATOR_TYPEOFDATA_FILE,
            NC_FIELDTYPE_FLOAT => NC_FIELDTYPE_FLOAT . ': ' . CLASSIFICATOR_TYPEOFDATA_FLOAT,
            NC_FIELDTYPE_DATETIME => NC_FIELDTYPE_DATETIME . ': ' . CLASSIFICATOR_TYPEOFDATA_DATETIME,
            NC_FIELDTYPE_MULTISELECT => NC_FIELDTYPE_MULTISELECT . ': ' . CLASSIFICATOR_TYPEOFDATA_MULTILIST,
            NC_FIELDTYPE_MULTIFILE => NC_FIELDTYPE_MULTIFILE . ': ' . CLASSIFICATOR_TYPEOFDATA_MULTIFILE,
        );
    }


    /**
     * Возвращает ID компонента для переадресации на первый дочерний раздел
     * @return int
     */
    public static function get_redirect_component_id() {
        $db = nc_db();
        return (int)$db->get_var("SELECT `Class_ID` FROM `Class` WHERE `Keyword`='netcat_base_subdivision_redirect' LIMIT 1");
    }

    /**
     * Получаем данные по компонентам "Товар" (или определенному компоненту),
     * включая название компонента и данные по полям
     *
     * @param int|null $component_id
     * @param bool $add_no_choose_field
     * @param bool $add_new_field_field
     * @return array
     */
    public static function get_goods_components_with_fields($component_id = null, $add_no_choose_field = false, $add_new_field_field = false) {
        $nc_core = nc_core::get_object();
        $nc_netshop = nc_netshop::get_instance();

        $query_where = null;

        if (!empty($component_id)) {
            // Найдем определенный компонент
            $query_where = " `f`.`Class_ID`={$component_id} ";
        } else {
            // Или выберем все товарные компоненты
            $goods_components_ids = $nc_netshop->get_goods_components_ids();
            $goods_components_ids = implode(',',  $goods_components_ids);
            $query_where = "`f`.`Class_ID` IN ({$goods_components_ids})";
        }

        // Выбор данных компонентов (компонента) "Товар"
        $data = $nc_core->db->get_results(
            "SELECT `Field_ID`,`f`.`Class_ID`,`Field_Name`,`Description`,`TypeOfData_ID`,`Format`,`f`.`Priority`,`Class_Name`
            FROM `Field` AS `f`
            JOIN `Class` AS `c`
            ON `f`.`Class_ID`=`c`.`Class_ID`
            WHERE {$query_where}",
            ARRAY_A
        );

        if (empty($data)) {
            return null;
        }

        $result = array();

        foreach ($data as $row) {
            $class_id = $row['Class_ID'];

            if (!array_key_exists($class_id, $result)) {
                $result[$class_id] = array(
                    'name' => $row['Class_Name'],
                    'fields' => array(),
                );

                // По хорошему бы вынести эту функциональность отсюда во внешнюю функцию. Потом.
                if ($add_no_choose_field) {
                    $field_flag = 0;
                    $result[$class_id]['fields'][$field_flag] = array(
                        'name' => nc_netshop_exchange_object::FAKE_FIELD_NO_FIELD,
                        'description' => '-- ' . NETCAT_MODULE_NETSHOP_EXCHANGE_FAKE_FIELD_NO_FIELD . ' --',
                        'type_of_data_id' => $field_flag,
                        'format' => '',
                        'priority' => -3,
                    );
                }

                if ($add_new_field_field) {
                    $field_flag = -1;
                    $result[$class_id]['fields'][$field_flag] = array(
                        'name' => nc_netshop_exchange_object::FAKE_FIELD_NEW_FIELD,
                        'description' => '-- ' . NETCAT_MODULE_NETSHOP_EXCHANGE_FAKE_FIELD_NEW_FIELD . ' --',
                        'type_of_data_id' => $field_flag,
                        'format' => '',
                        'priority' => -2,
                    );
                }

                $field_flag = -2;
                $result[$class_id]['fields'][$field_flag] = array(
                    'name' => nc_netshop_exchange_object::FAKE_FIELD_PARENT_FIELD,
                    'description' => NETCAT_MODULE_NETSHOP_EXCHANGE_FAKE_FIELD_PARENT_FIELD,
                    'type_of_data_id' => $field_flag,
                    'format' => '',
                    'priority' => -1,
                );
            }

            $result[$class_id]['fields'][$row['Field_ID']] = array(
                'name' => $row['Field_Name'],
                'description' => $row['Description'],
                'type_of_data_id' => $row['TypeOfData_ID'],
                'format' => $row['Format'],
                'priority' => $row['Priority'],
            );
        }

        // Сортировка полей
        foreach ($result as &$component) {
            uasort($component['fields'], function($a, $b) {
                return (int)$a['priority'] - (int)$b['priority'];
            });
        }
        unset($component);

        return $result;
    }

    /**
     * Получаем данные по определенному компоненту "Товар"
     *
     * @param $component_id
     * @return array
     */
    public static function get_goods_component_with_fields($component_id) {
        $components = self::get_goods_components_with_fields($component_id);
        return $components[$component_id];
    }

    /**
     * Преобразование строки в вид, применимый в качестве ключевого слова
     *
     * @param $string - Исходная строка
     * @param $delimiter - Разделитель слов
     * @return string
     */
    public static function string_to_keyword($string, $delimiter = '_') {
        $keyword = nc_transliterate($string, true);
        $keyword = preg_replace('/[^a-zA-Z0-9]/i', ' ', $keyword);
        $keyword = preg_replace("/\s+/", ' ', $keyword);
        $keyword = str_replace(' ', $delimiter, $keyword);
        $keyword = nc_strtolower($keyword);
        return $keyword;
    }

    /**
     * Генерация ключевого слова из строки
     *
     * @param $string - Исходная строка
     * @param $delimiter - Разделитель слов
     * @param $possible_prefix - Возможный префикс ключевого слова на тот случай,
     *                           если ключевое слово состоит только из цифр
     * @return string
     */
    public static function generate_keyword($string, $delimiter = '_', $possible_prefix = 'keyword') {
        $keyword = self::string_to_keyword($string, $delimiter);
        // Если после преобразований ключевое слово всё еще состоит из цифр
        if ($possible_prefix !== null && ctype_digit($keyword)) {
            // То добавим возможный префикс и преобразуем снова
            $keyword = $possible_prefix . $delimiter . $keyword;
            $keyword = self::string_to_keyword($keyword, $delimiter);
        }
        return $keyword;
    }

    /**
     * Генерация возможного названия компонента из его имени
     *
     * @param string|null $name - Название компонента
     * @return string
     */
    public static function get_component_possible_name($name = null) {
        $db = nc_db();

        if (empty($name)) {
            $name = NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_COMPONENT_NAME;
        }

        // Подберём доступный
        $original = $name;
        $counter = 1;
        while (true) {
            $component_id = $db->get_var("SELECT `Class_ID` FROM `Class` WHERE `Class_Name`='{$name}'");
            if (!empty($component_id)) {
                $name = $original . ' ' . $counter++;
                continue;
            }
            break;
        }

        return $name;
    }

    /**
     * Генерация возможного ключевого слова компонента из его имени
     *
     * @param $name - Название компонента
     * @return string
     */
    public static function get_component_possible_keyword($name) {
        $db = nc_db();

        $delimiter = '_';
        $keyword = self::generate_keyword($name, $delimiter, null);
        $prefix = 'goods';
        if (substr($keyword,0, strlen($prefix)) != $prefix) {
            $keyword = $prefix . $delimiter . $keyword;
        }
        // Подберём доступный
        $original = $keyword;
        $counter = 1;
        while (true) {
            $component_id = $db->get_var("SELECT `Class_ID` FROM `Class` WHERE `Keyword`='{$keyword}'");
            if (!empty($component_id)) {
                $keyword = $original . $delimiter . $counter++;
                continue;
            }
            break;
        }

        return $keyword;
    }

    /**
     * Генерация возможного названия раздела из его имени
     *
     * @param $name - Название раздела
     * @param $parent_sub_id - ID родительского раздела
     * @return string
     */
    public static function get_subdivision_possible_name($name, $parent_sub_id = 0) {
        $db = nc_db();

        if (empty($name)) {
            $name = NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_SUBDIVISION_NAME;
        }

        // Подберём доступный
        $original = $name;
        $counter = 1;
        while (true) {
            $subdivision_id = $db->get_var("SELECT `Subdivision_ID` FROM `Subdivision` WHERE `Subdivision_Name`='{$name}' AND `Parent_Sub_ID`='{$parent_sub_id}'");
            if (!empty($subdivision_id)) {
                $name = $original . ' ' . $counter++;
                continue;
            }
            break;
        }

        return $name;
    }

    /**
     * Генерация возможного ключевого слова раздела из его имени
     * @param $name - Название раздела
     * @param $parent_sub_id - ID родительского раздела
     * @return string
     */
    public static function get_subdivision_possible_keyword($name, $parent_sub_id = 0) {
        $db = nc_db();

        $delimiter = '-';
        $keyword = self::generate_keyword($name, $delimiter, 'subdivision');

        // Подберём доступный
        $original = $keyword;
        $counter = 1;
        while (true) {
            $subdivision_id = $db->get_var("SELECT `Subdivision_ID` FROM `Subdivision` WHERE `EnglishName`='{$keyword}' AND `Parent_Sub_ID`='{$parent_sub_id}'");
            if (!empty($subdivision_id)) {
                $keyword = $original . $delimiter . $counter++;
                continue;
            }
            break;
        }

        return $keyword;
    }

    /**
     * Генерация возможного ключевого слова инфоблока из его имени
     *
     * @param $name - Название инфоблока
     * @param $subdivision_id - ID раздела
     * @return string
     */
    public static function get_sub_class_possible_keyword($name, $subdivision_id) {
        $keyword = self::generate_keyword($name, '-', 'infoblock');
        $keyword = nc_core::get_object()->sub_class->get_available_english_name($subdivision_id, $keyword);
        return $keyword;
    }

    /**
     * Преобразование ключа объекта маппинга в данные
     * @param $item_key
     * @return array
     */
    public static function item_key_to_data($item_key) {
        $data = explode('|', $item_key);
        return array(
            'file_path' => nc_array_value($data, 0),
            'scope' => nc_array_value($data, 1),
            'scope_name' => nc_array_value($data, 2),
        );
    }

    /**
     * Получение списка разделов для данного сайта
     * @param int $site_id
     * @return array
     */
    public static function get_subdivisions($site_id) {
        $db = nc_db();

        $result = $db->get_results(
            "SELECT `Subdivision_ID` AS `id`,`Parent_Sub_ID` AS `parent_id`,`Subdivision_Name` AS `name`
            FROM `Subdivision`
            WHERE `Catalogue_ID`={$site_id}
            ORDER BY `Parent_Sub_ID`,`Priority`",
            ARRAY_A
        );

        if (empty($result)) {
            return null;
        }
        return array(
            0 => array(
                'name' => $db->get_var("SELECT `Catalogue_Name` FROM `Catalogue` WHERE `Catalogue_ID`='{$site_id}'") . ' (Положить в корень)',
                'children' => self::build_tree($result, 0, 'id', 'parent_id'),
            ),
        );
    }

    /**
     * Построение многомерного массива-дерева из одномерного списка
     * @param array $array
     * @param int $parent_val
     * @param string $primary_key
     * @param string $parent_key
     * @return array
     */
    public static function build_tree($array, $parent_val = 0, $primary_key = 'id', $parent_key = 'parent_id') {
        // Построим функцию фильтрации по родителям
        $filter_func = function($parent_key, $parent_val) {
            return function($array) use ($parent_key, $parent_val) {
                return $array[$parent_key] == $parent_val;
            };
        };
        $filter = $filter_func($parent_key, $parent_val);

        // Дети и остальные
        $children = array_filter($array, $filter);
        $other = @array_diff_assoc($array, $children);

        // Построим дерево
        $result = array();

        foreach ($children as $child) {
            $id = $child[$primary_key];
            unset($child[$primary_key]);

            $child_children = self::build_tree($other, $id, $primary_key, $parent_key);
            $child = !empty($child_children) ? array_merge($child, array('children' => $child_children)) : $child;

            $result[$id] = $child;
        }

        return $result ? $result : null;
    }

    /**
     * Построение select-списка из многомерного массива
     * @param array $tree
     * @param string $name
     * @param int $default
     * @param int $level
     * @return string
     */
    public static function print_tree_as_select($tree, $name, $default = 0, $level = 0) {
        $result = $level == 0 ? '<select name="' . $name . '" id="nc-netshop-exchange-subdivision">' : '';

        foreach ($tree as $id => $data) {
            $selected = $id == $default ? 'selected ' : null;
            $id_text = $id > 0 ? "{$id}. " : null;
            $result .= '<option value="' . $id . '" ' . $selected . '>' . str_repeat('--- ', $level) . $id_text . $data['name'] . '</option>';

            if (!empty($data['children'])) {
                $result .= self::print_tree_as_select($data['children'], $name, $default, $level + 1);
            }
        }

        return $result ? $result . ($level == 0 ? '</select>' : '') : null;
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function exchange_type_to_name($type) {
        $tmp = array(
            nc_netshop_exchange_object::TYPE_IMPORT => NETCAT_MODULE_NETSHOP_EXCHANGE_TYPE_IMPORT,
            nc_netshop_exchange_object::TYPE_EXPORT => NETCAT_MODULE_NETSHOP_EXCHANGE_TYPE_EXPORT,
        );

        return $tmp[$type];
    }

    /**
     * @param $format
     * @return mixed
     */
    public static function exchange_format_to_name($format) {
        $tmp = array(
            nc_netshop_exchange_import::FORMAT_CSV   => strtoupper(nc_netshop_exchange_import::FORMAT_CSV),
            nc_netshop_exchange_import::FORMAT_XLS   => strtoupper(nc_netshop_exchange_import::FORMAT_XLS),
            nc_netshop_exchange_import::FORMAT_YML   => strtoupper(nc_netshop_exchange_import::FORMAT_YML),
            nc_netshop_exchange_import::FORMAT_CML   => 'CommerceML',
            nc_netshop_exchange_import::FORMAT_PRICE => NETCAT_MODULE_NETSHOP_EXCHANGE_TYPE_PRICE,
        );

        return $tmp[$format];
    }

    /**
     * @param $mode
     * @return mixed
     */
    public static function exchange_mode_to_name($mode) {
        $tmp = array(
            nc_netshop_exchange_object::MODE_MANUAL    => NETCAT_MODULE_NETSHOP_EXCHANGE_MODE_MANUAL,
            nc_netshop_exchange_object::MODE_AUTOMATED => NETCAT_MODULE_NETSHOP_EXCHANGE_MODE_AUTOMATED,
        );

        return $tmp[$mode];
    }

    /**
     * @param $action
     * @return mixed
     */
    public static function log_action_to_name($action) {
        $tmp = array(
            nc_netshop_exchange_log::ACTION_ERROR => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_ERROR,
            nc_netshop_exchange_log::ACTION_CRITICAL_ERROR => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_CRITICAL_ERROR,
            nc_netshop_exchange_log::ACTION_TYPE_CONVERSION => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_TYPE_CONVERSION,
            nc_netshop_exchange_log::ACTION_LIST_INSERTION => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_LIST_INSERTION,
            nc_netshop_exchange_log::ACTION_REPORT_HAS_BEEN_SENT => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_REPORT_HAS_BEEN_SENT,
            nc_netshop_exchange_log::ACTION_FILE_NOT_FOUND => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_FILE_NOT_FOUND,
            nc_netshop_exchange_log::ACTION_OBJECT_ADDED => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_OBJECT_ADDED,
            nc_netshop_exchange_log::ACTION_OBJECT_NOT_ADDED => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_OBJECT_NOT_ADDED,
            nc_netshop_exchange_log::ACTION_OBJECT_UPDATED => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_OBJECT_UPDATED,
            nc_netshop_exchange_log::ACTION_OBJECT_NOT_UPDATED => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_OBJECT_NOT_UPDATED,
        );

        return $tmp[$action];
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function log_type_to_color($type) {
        $tmp = array(
            nc_netshop_exchange_log::TYPE_DANGER => 'rgba(217, 83, 79, 0.3)',
            nc_netshop_exchange_log::TYPE_WARNING => 'rgba(240, 173, 78, 0.3)',
            nc_netshop_exchange_log::TYPE_DEFAULT => '#f7f7f7',
            nc_netshop_exchange_log::TYPE_INFO => 'rgba(91, 192, 222, 0.3)',
            nc_netshop_exchange_log::TYPE_SUCCESS => 'rgba(92, 184, 92, 0.3)',
        );

        return $tmp[$type];
    }

    public static function print_upload_info() {
        $max_upload_files_count = ini_get('max_file_uploads');
        $max_upload_files_size = nc_bytes2size(self::parse_size(ini_get('post_max_size')));
        $max_upload_file_size = nc_bytes2size(self::parse_size(ini_get('upload_max_filesize')));
        return sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_UPLOAD_INFO, $max_upload_files_count, $max_upload_files_size, $max_upload_file_size);
    }

    public static function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * Сохранение файлов для определенной записи в компоненте в поле "MULTIFILE"
     * @todo заменить на nc_multifield_*
     *
     * @param int $component_id
     * @param int $message_id
     * @param string $field_name
     * @param array $files_paths
     * @param bool $delete_files
     * @return bool $status
     */
    public static function set_files($component_id, $message_id, $field_name, $files_paths, $delete_files = true) {
        // Проверим наличие файлов
        $files_count = count($files_paths);

        for ($i = 0; $i < $files_count; $i++) {
            $files_paths[$i] = nc_standardize_path_to_file($files_paths[$i]);

            if (file_exists($files_paths[$i]) && !is_dir($files_paths[$i])) {
                continue;
            }

            unset($files_paths[$i]);
        }

        if (count($files_paths) == 0) {
            return false;
        }

        // Восстановим индексы, на тот случай если из массивы были удалены файлы
        $files_paths = array_values($files_paths);

        // Проверяем, есть ли в указанном компоненте такое поле с нужным типом
        $nc_core = nc_core::get_object();
        $component = $nc_core->get_component($component_id);
        if (!$component->has_field($field_name, NC_FIELDTYPE_MULTIFILE)) {
            return false;
        }

        // Id поля
        $field_id = $component->get_field($field_name, 'id');

        // Удалим старые файлы если есть
        $current_files = $nc_core->db->get_results("SELECT `ID`,`Path`,`Preview` FROM `Multifield` WHERE `Field_ID`='{$field_id}' AND `Message_ID`='{$message_id}'");
        if (count($current_files)) {
            $current_files_ids = array();

            foreach ($current_files as $current_file) {
                $current_files_ids[] = $current_file->ID;

                // Удалим файлы
                $file_path = $nc_core->DOCUMENT_ROOT . $current_file->Path;
                if (file_exists($file_path) && !is_dir($file_path)) {
                    unlink($file_path);
                }
                $file_path = $nc_core->DOCUMENT_ROOT . $current_file->Preview;
                if (file_exists($file_path) && !is_dir($file_path)) {
                    unlink($file_path);
                }
            }

            // Удалим файлы из базы
            $current_files_ids = implode(',', $current_files_ids);
            $nc_core->db->query("DELETE FROM `Multifield` WHERE `ID` IN ({$current_files_ids})");
        }

        // Папка загрузки файла
        $dst_files_folder_relative_path = "/netcat_files/multifile/{$field_id}";
        $dst_files_folder_path = $nc_core->DOCUMENT_ROOT . $dst_files_folder_relative_path;
        $nc_core->files->create_dir($dst_files_folder_path);

        // Загрузим новые файлы
        foreach ($files_paths as $i=>$src_file_path) {
            $src_file_name = pathinfo($src_file_path, PATHINFO_BASENAME);

            // Название и путь к файлу
            $dst_file_name = nc_transliterate($src_file_name);
            $dst_file_name = nc_get_filename_for_original_fs($dst_file_name, $dst_files_folder_path . '/', array());
            $dst_file_path = $dst_files_folder_path . '/' . $dst_file_name;

            // Добавим новый файл
            $function = $delete_files ? 'rename' : 'copy';
            $function($src_file_path, $dst_file_path);

            // Размер итогового файла
            $dst_file_size = filesize($dst_file_path);

            // Добавим в базу
            $dst_file_db_path = str_replace($nc_core->DOCUMENT_ROOT, '', $dst_file_path);
            $nc_core->db->query(
                "INSERT INTO `Multifield`
                SET `Field_ID`='{$field_id}',
                    `Message_ID`= '{$message_id}',
                    `Priority`='{$i}',
                    `Size`='{$dst_file_size}',
                    `Path`='{$dst_file_db_path}'"
            );
        }

        return true;
    }

    /**
     * Конвертирует МБ (вообще, конечно, мибибайты, но ...) в байты
     * @param number $mb - МБ
     * @return number $multiplier - Б
     */
    public static function mb_to_bytes($mb) {
        return $mb * 1024 * 1024;
    }

    /**
     * Возвращает множитель в зависимости от значения и переданной таблицы множителей
     * @param number $value
     * @param array $multipliers
     * @return number $multiplier
     */
    public static function get_multiplier_by_value($value, $multipliers) {
        $result = null;
        foreach ($multipliers as $boundary => $multiplier) {
            if ($value >= $boundary) {
                $result = $multiplier;
            }
        }
        return $result;
    }
}