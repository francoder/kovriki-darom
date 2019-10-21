<?php

/**
 * Генератор изображений по заданным параметрам
 * Class nc_image_generator
 */
class nc_image_generator extends nc_image_base {
    /**
     * Создает экземпляр класса генератора изображений
     * @param array $parameters
     * @return self
     */
    public static function from_parameters(array $parameters) {
        // Соберем объект
        $object = new self();
        $object->set_entity($parameters['entity']);
        $object->set_field($parameters['field']);
        $object->set_object($parameters['object']);
        if ($parameters['file_id']) {
            $object->set_file_id($parameters['file_id']);
        }
        $object->set_extension($parameters['extension']);
        $resize = $parameters['resize'];
        $object->set_resize($resize[0], $resize[1], $resize[2]);
        $crop = $parameters['crop'];
        $object->set_crop($crop[0], $crop[1], $crop[2], $crop[3]);
        if (!empty($parameters['watermark_http_path'])) {
            $object->set_watermark($parameters['watermark_http_path'], $parameters['watermark_mode']);
        }
        $object->set_scale($parameters['scale']);
        $object->find_original_url();
        return $object;
    }

    /**
     * Парсинг параметров для генерации изображения по заданному URL файла
     * @param string $url_path       - Путь (без GET-параметров)
     * @param array $url_parameters  - Массив параметров
     * @return array
     */
    public static function get_parameters_from_url($url_path, array $url_parameters = array()) {
        $url_path = str_replace(self::generated_images_http_path() . '/', '', $url_path);
        $path_parameters = explode('/', $url_path);
        // Соберем данные
        $file_id = null;
        if (count($path_parameters) == 5) {
            $hash_and_ext_info = $path_parameters[4];
        } else {
            $file_id = $path_parameters[4];
            $hash_and_ext_info = $path_parameters[5];
        }
        $info = pathinfo($hash_and_ext_info);
        $resize = explode('x', $path_parameters[2]);
        $resize[] = nc_array_value($url_parameters, 'resize_mode', nc_ImageTransform::RESIZE_TO_BEST_FIT_WITH_CROP);
        $scale = isset($url_parameters['scale']) ? $url_parameters['scale'] : null;
        if ($scale) {
            $scale = self::standardize_float_value($scale);
            $scale = floatval($scale);
        }
        return array(
            'entity' => $path_parameters[0],
            'field' => $path_parameters[1],
            'resize' => $resize,
            'object' => $path_parameters[3],
            'parameters_hash' => $info['filename'],
            'file_id' => $file_id,
            'extension' => $info['extension'],
            'crop' => explode(':', $url_parameters['crop']),
            'result_hash' => $url_parameters['hash'],
            'watermark_http_path' => isset($url_parameters['wm_p']) ? $url_parameters['wm_p'] : null,
            'watermark_mode' => isset($url_parameters['wm_m']) ? $url_parameters['wm_m'] : null,
            'scale' => $scale,
        );
    }

    /**
     * Проверка хэша (защита от нежелательных генераций)
     * @param string $result_hash
     * @return boolean
     */
    public function validate_hash($result_hash) {
        return $result_hash == $this->calculate_result_hash();
    }

    /**
     * Генерация изображений по заданным параметрам
     * @return null|string
     */
    public function generate() {
        $nc_core = nc_core::get_object();
        // Проверим наличие оригинальной картинки
        if (!$this->file_path) {
            return null;
        }
        $original_image_absolute_path = $nc_core->DOCUMENT_ROOT . $nc_core->SUB_FOLDER . $this->file_path;
        if (!file_exists($original_image_absolute_path) || is_dir($original_image_absolute_path)) {
            return null;
        }
        // Сгенерируем папку и скопируем файл
        $image_http_path = $this->generate_image_http_path();
        $image_absolute_path = $nc_core->DOCUMENT_ROOT . $nc_core->SUB_FOLDER . $image_http_path;
        $image_dir_path = dirname($image_absolute_path);
        if (!file_exists($image_dir_path)) {
            mkdir($image_dir_path, $nc_core->DIRCHMOD, true);
        }
        if (!copy($original_image_absolute_path, $image_absolute_path)) {
            return null;
        }
        // Обработаем файл
        $this->prepare($image_absolute_path);
        return $image_absolute_path;
    }

    /**
     * Обработка скопированного оригинального изображения
     * @param string $file_path
     */
    protected function prepare($file_path) {
        $scale = $this->resize($file_path);
        $this->crop($file_path, $scale);
        $this->watermark($file_path, $scale);
    }

    /**
     * Обрезка изображения по заданным параметрам
     * @param string $file_path
     * @param float $scale
     */
    protected function crop($file_path, $scale) {
        if (!$this->has_crop_parameters()) {
            return;
        }

        $x = floor((int)$this->crop['x'] * $scale);
        $y = floor((int)$this->crop['y'] * $scale);
        $width = floor((int)$this->crop['width'] * $scale);
        $height = floor((int)$this->crop['height'] * $scale);

        nc_ImageTransform::imgCrop($file_path, $file_path, $x, $y, $x + $width, $y + $height);
    }

    /**
     * Изменение размера изображения по заданным параметрам
     * @param string $file_path
     * @return float|int применённый коэффициент масштабирования
     */
    protected function resize($file_path) {
        if (!$this->has_resize_parameters()) {
            return 1;
        }

        $width = (int)$this->resize['width'];
        $height = (int)$this->resize['height'];
        $mode = $this->resize['mode'];

        $scale = $this->get_actual_scale($file_path, $width, $height);
        $width = floor($width * $scale);
        $height = floor($height * $scale);

        nc_ImageTransform::imgResize($file_path, $file_path, $width, $height, $mode);
        return $scale;
    }

    /**
     * Возвращает коэффициент масштабирования, который будет применён к файлу,
     * исходя из запрошенного scale и фактических размеров изображения
     * (произведение коэффициента масштабирования и запрошенной ширины или высоты
     * не может быть больше, чем размеры изображения)
     * @param string $file_path путь к файлу
     * @param int $requested_width запрошенная ширина
     * @param int $requested_height запрошенная высота
     * @return float|int применяемый коэффициент масштабирования
     */
    protected function get_actual_scale($file_path, $requested_width, $requested_height) {
        $scale = $this->scale ?: 1;

        list($src_width, $src_height) = $this->get_image_size($file_path);
        if ($src_width < $requested_width * $scale) {
            $scale = $src_width / $requested_width;
        }
        if ($src_height < $requested_height * $scale) {
            $scale = $src_height / $requested_height;
        }

        return $scale;
    }

    /**
     * Наложение водяного знака на изображение по заданным параметрам
     * @param string $file_path
     * @param int|float $scale
     */
    protected function watermark($file_path, $scale) {
        $nc_core = nc_core::get_object();
        $watermark = $this->watermark['http_path'];
        $mode = $this->watermark['mode'];
        if ($watermark) {
            $watermark = '/' . ltrim($watermark, '/');
            $watermark_path = $nc_core->DOCUMENT_ROOT . $nc_core->SUB_FOLDER . $watermark;
            $watermark_exists = file_exists($watermark_path) && !is_dir($watermark_path);
            if ($watermark_exists) {
                nc_ImageTransform::putWatermark_file($file_path, $watermark, $mode, null, $scale);
            }
        }
    }

    /**
     * Получение размера изображения
     * @param $file_path
     * @return array|null
     */
    protected function get_image_size($file_path) {
        $img_size = @getimagesize($file_path);
        if ($img_size === false) {
            return null;
        }
        return array($img_size[0], $img_size[1]);
    }

    /**
     * Поиск пути к оригинальной картинке по заданным параметрам (сущность, поле, объект, файл)
     */
    protected function find_original_url() {
        $nc_core = nc_core::get_object();
        $entity = $this->entity;
        if (!$entity) {
            return;
        }
        $result = null;
        // Компонент?
        if (is_numeric($entity)) {
            $field = (int)$this->field;
            $object = (int)$this->object;
            if ($field && $object) {
                $file_id = (int)$this->file_id;
                if (!$file_id) {
                    // Из поля "Файл"
                    $result = nc_file_path($entity, $object, $field);
                } else {
                    // Из поля "Множественная загрузка файлов"
                    $result = $nc_core->db->get_var(
                        "SELECT `Path`
                         FROM `Multifield`
                         WHERE `Message_ID`='{$object}' AND `Field_ID`='{$field}' AND `ID`='{$file_id}'"
                    );
                }
            }
        } else {
            // Настройки инфоблока?
            if ($entity == 'class_settings') {
                // Получим настройки инфоблока
                $sub_class_id = (int)$this->object;
                $field_name = $this->field;
                $sub_class_data = $nc_core->sub_class->get_by_id($sub_class_id);
                $custom_settings = $sub_class_data['CustomSettings'];
                $class_id = $sub_class_data['Class_ID'];
                $custom_settings_template = $nc_core->component->get_by_id($class_id, 'CustomSettingsTemplate');
                $a2f = new nc_a2f($custom_settings_template);
                $a2f->set_value($custom_settings);
                // Получим данные запрашиваемого поля
                $custom_settings_fields_data = $a2f->get_values_as_array();
                $custom_settings_field_data = $custom_settings_fields_data[$field_name];
                $result = $custom_settings_field_data['path'];
            }
            // Настройки макета дизайна?
            else if ($entity == 'template_settings') {
                // Получим настройки макета дизайна для раздела
                $subdivision_id = (int)$this->object;
                $field_name = $this->field;
                // Получим данные запрашиваемого поля
                $custom_settings_fields_data = $nc_core->subdivision->get_template_settings($subdivision_id);
                $custom_settings_field_data = $custom_settings_fields_data[$field_name];
                $result = $custom_settings_field_data['path'];
            }
            // Системные таблицы?
            else {
                $field = (int)$this->field;
                $object = (int)$this->object;
                $result = nc_file_path(ucfirst($entity), $object, $field);
            }
        }
        $this->file_path = $result;
    }

    /**
     * Гибкое удаление сгенерированных изображений
     * @param null|integer|string $entity  - Сущность (Компонент/Системная таблица)
     *                                       [например: 169, 'subdivision', 'class_settings']
     * @param null|integer|string $field   - Поле в сущности
     *                                       [например: 582 или 'Image' - для компонентов и системных таблиц
     *                                                  'Image' - для полей пользовательских настроек]
     * @param null|integer $object  - Объект в сущности
     * @param null|integer $file_id    - ID файла в мультифайловом поле (если поле мультифайл)
     */
    public static function remove_generated_images($entity = null, $field = null, $object = null, $file_id = null) {
        $nc_core = nc_core::get_object();
        $generated_images_http_path = nc_image_base::generated_images_http_path();
        $generated_images_path = $nc_core->DOCUMENT_ROOT . $nc_core->SUB_FOLDER . $generated_images_http_path;
        $current_dir = $generated_images_path;
        // Удаление всех сгенерированных изображений
        if (!$entity) {
            if (file_exists($current_dir) && is_dir($current_dir)) {
                nc_delete_dir($current_dir);
            }
            return;
        }
        // Удаление сгенерированных изображений сущности
        $current_dir .= '/' . $entity;
        if (!$field) {
            if (file_exists($current_dir) && is_dir($current_dir)) {
                nc_delete_dir($current_dir);
            }
            return;
        }
        // Удаление в сущности изображение определенного поля
        $field = self::get_field_dir_name_from_entity_and_field($entity, $field);
        $current_dir .= '/' . $field;
        if (!$object) {
            if (file_exists($current_dir) && is_dir($current_dir)) {
                nc_delete_dir($current_dir);
            }
            return;
        }
        // Удаление в сущности изображений объекта из определенного поля
        $resize_dirs = glob($current_dir . '/*', GLOB_ONLYDIR);
        if ($resize_dirs) {
            foreach ($resize_dirs as $current_dir) {
                $current_dir .= '/' . $object;
                // Если "Множественная загрузка файлов", то удаляем картинки только по ID картинки
                if ($file_id) {
                    $current_dir .= '/' . $file_id;
                }
                if (file_exists($current_dir) && is_dir($current_dir)) {
                    nc_delete_dir($current_dir);
                }
            }
        }
    }

    /**
     * Получение  поля по заданному entity и field.
     * Для компонента или системной таблицы - ID поля.
     * Для прочих ситуаций - название поля.
     * @param integer|string $entity
     * @param integer|string $field
     * @return array|null|string
     */
    protected static function get_field_dir_name_from_entity_and_field($entity, $field) {
        $system_tables = self::get_system_table_names();
        array_walk($system_tables, function (&$val) {
            $val = strtolower($val);
        });
        unset($val);
        $entity_is_id = ctype_digit($entity);
        $is_component_or_system_table = $entity_is_id || in_array($entity, $system_tables);
        if (!$is_component_or_system_table) {
            return $field;
        }
        $field_is_id = ctype_digit($field);
        if ($field_is_id) {
            return $field;
        }
        $component_or_system_table_keyword = $entity_is_id ? $entity : ucfirst($entity);
        $component_or_system_table = new nc_component($component_or_system_table_keyword);
        $field = $component_or_system_table->get_field($field, 'id');
        return $field;
    }

    /**
     * Возвращает латинские имена системных таблиц
     * @return array
     */
    protected static function get_system_table_names() {
        $nc_core = nc_core::get_object();
        $result = array(
            // Системная таблица "Сайт"
            $nc_core->get_system_table_name_by_id(1),
            // Системная таблица "Раздел"
            $nc_core->get_system_table_name_by_id(2),
            // Системная таблица "Пользователь"
            $nc_core->get_system_table_name_by_id(3),
            // Системная таблица "Макет дизайна"
            $nc_core->get_system_table_name_by_id(4),
        );
        return $result;
    }

    /**
     * Получение ключевого слова сущности по ID класса или ключевого слова или ID системной таблицы
     * @param null|integer $class_id [напр: 10]
     * @param null|string|integer $sys_table [напр: 1 или 3 или Catalogue]
     * @return null|string
     */
    public static function get_entity_by_class_id_or_system_table($class_id = null, $sys_table = null) {
        if ($class_id && is_numeric($class_id)) {
            return $class_id;
        }
        if ($sys_table) {
            if (is_numeric($sys_table)) {
                $nc_core = nc_core::get_object();
                $sys_table = $nc_core->get_system_table_name_by_id($sys_table);
            }
            return strtolower($sys_table);
        }
        return null;
    }
}