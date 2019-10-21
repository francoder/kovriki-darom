<?php

class nc_netshop_exchange_wizard_admin_controller extends nc_netshop_exchange_admin_controller {
    protected $ui_config_base_path = 'exchange.wizard';

    protected $core;

    protected function init() {
        parent::init();

        $this->core = nc_Core::get_object();

        foreach (array('start', 'setup_automated_cml', 'selection', 'mapping', 'config', 'report') as $action) {
            $this->bind($action, array('action', 'site_id', 'object_id', 'phase'));
        }
        foreach (array('start_save', 'setup_automated_cml_save', 'selection_save', 'config_save') as $action) {
            $this->bind($action, array('object_id'));
        }
        $this->bind('mapping_save', array('object_id', 'phase'));
    }

    /**
     * Добавление кнопки "Назад"
     */
    private function add_back_button() {
        $this->ui_config->actionButtons[] = array(
            "id" => "history_back",
            "caption" => NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_GO_BACK,
            "action" => "history.back(1)",
            "align" => "left"
        );
    }

    /**
     * Добавление кнопки "Далее"
     * @param string $text
     */
    private function add_next_button($text = NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_NEXT) {
        $this->ui_config->actionButtons[] = array(
            "id" => "submit_form",
            "caption" => $text,
            "action" => "mainView.submitIframeForm()"
        );
    }

    /**
     * Получить view
     * @param $action
     * @param $site_id
     * @param $object_id
     * @param $phase
     * @return nc_ui_view
     */
    private function get_view($action, $site_id, $object_id, $phase) {
        $params = array($action, $site_id, $object_id, $phase);
        $params = implode(',', $params);
        $this->ui_config->set_exchange_location_suffix(".wizard({$params})");

        $view = $this->view('object_wizard');

        $view->controller = 'exchange_wizard';
        $view->current_action = $action;
        $view->phase = $phase;
        $view->object_id = -1;

        if ($object_id > 0) {
            $view->object_id = $object_id;
        }

        return $view;
    }

    /**
     * Первоначальная настройка объекта обмена
     * @param $action
     * @param $site_id
     * @param int $object_id
     * @param int $phase
     * @return nc_ui_view
     */
    public function action_start($action, $site_id, $object_id = -1, $phase = 1) {
        $view = $this->get_view($action, $site_id, $object_id, $phase);
        $nc_core = nc_core::get_object();
        $netshop = nc_netshop::get_instance();

        $disabled_formats = array();
        if (!$netshop->is_feature_enabled('import_cml')) {
            $disabled_formats[] = nc_netshop_exchange_import::FORMAT_CML;
        }

        $view->step = NETCAT_MODULE_NETSHOP_EXCHANGE_STEP_TYPE_AND_FORMAT;
        $view->next_action = 'start_save';
        $view->has_files = false;
        $view->upload_error = nc_netshop_exchange_session::get('upload_error');
        $view->has_zip_extension = class_exists('ZipArchive');
        $view->upload_info = nc_netshop_exchange_helper::print_upload_info();
        $view->nc_core = $nc_core;
        $view->formats = nc_netshop_exchange_helper::get_formats();
        $view->modes = nc_netshop_exchange_helper::get_modes();
        $view->disabled_formats = $disabled_formats;
        nc_netshop_exchange_session::delete('upload_error');

        if ($object_id > 0) {
            $object = nc_netshop_exchange_import::by_id($object_id);
            $view->object = $object;
            $view->has_files = $object->has_acceptable_files();
        }

        $this->add_back_button();
        $this->add_next_button();

        return $view;
    }

    /**
     * Сохранение (создание) объекта обмена
     * @param int $object_id
     * @return boolean
     * @throws nc_netshop_exchange_exception
     */
    public function action_start_save($object_id = -1) {
        // Создадим или обновим объект
        $object_data = $this->core->input->fetch_post('object');
        $just_created = false;
        if ($object_id <= 0) {
            $object_data['checked'] = false;
            $object_data['catalogue_id'] = $this->site_id;
            $object_data['cron_key'] = 'key_' . mt_rand(10000000, 99999999);
            $object = nc_netshop_exchange_object::from_array($object_data);
            $object->save();

            $object_id = (int)$object->get_id();
            $just_created = true;
        } else {
            $object = nc_netshop_exchange_object::by_id($object_id);
            $object['type'] = $object_data['type'];
            $object['format'] = $object_data['format'];
            $object['mode'] = $object_data['mode'];
            $object->save();
        }

        // В зависимости от режима импорта
        switch ($object['mode']) {
            // Если ручной/полуручной режим
            case nc_netshop_exchange_object::MODE_MANUAL: {
                // Загрузим файлы и проверим есть ли они
                $object = nc_netshop_exchange_import::by_id($object_id);
                $uploaded_files = (count($_FILES['files']['error']) == 1 && $_FILES['files']['error'][0] == UPLOAD_ERR_NO_FILE) ? null : $_FILES['files'];
                $upload_file_url = $this->core->input->fetch_post_get('file_url');
                $uploaded = false;
                if (!empty($uploaded_files)) {
                    $uploaded = $object->upload_files($uploaded_files, true);
                } else if (!empty($upload_file_url)) {
                    $uploaded = $object->upload_file_from_url($upload_file_url, true);
                    // Если загрузка с удаленного Url была успешна - то запомним Url
                    if ($uploaded) {
                        $object->set('remote_file_url', $upload_file_url);
                        $object->save();
                    }
                }
                nc_netshop_exchange_session::set('just_uploaded', $uploaded);
                break;
            }
            // Если автоматический режим
            case nc_netshop_exchange_object::MODE_AUTOMATED: {

                break;
            }
        }

        // Есть ли файлы для настройки маппинга?
        $has_files = $object->has_acceptable_files();
        if (!$has_files) {
            switch ($object['mode']) {
                // Если в ручном режиме не удалось получить файлы для обмена - перейдем обратно, не сохраняя
                // объект импорта
                case nc_netshop_exchange_import::MODE_MANUAL:
                    $upload_error = array(
                        'acceptable_files_extensions' => implode(', ', $object::get_acceptable_files_extensions()),
                        'type' => $object['type'],
                        'format' => $object['format'],
                    );
                    nc_netshop_exchange_session::set('upload_error', $upload_error);
                    if (!$object['checked']) {
                        try {
                            $object->delete();
                        }
                        catch (Exception $e) {}
                    }
                    // Перейдем обратно на начало для повторной загрузки
                    $this->redirect_to_index_action('start', "&object_id=-1&phase=1");
                    break;

                // Если в автоматическом режиме, то сохраняем объект, но переходим на инструкцию
                // по настройке автоматического обмена
                case nc_netshop_exchange_object::MODE_AUTOMATED:
                    // Очистим папку перед приёмом удалённых файлов
                    $object->clean_folder();
                    $this->redirect_to_index_action('setup_automated_cml', "&object_id={$object_id}&phase=1");
                    break;

            }
        }

        // Соберем данные по импорту, чтобы знать что нам предстоит маппить
        $object->get_mapping()->start();

        // Повторная настройка обмена?
        if (!$just_created) {
            // Перейдем на действие выбора что конкретно сопоставлять.
            $this->redirect_to_index_action('selection', "&object_id={$object_id}&phase=1");
        }

        // Перейдем к настройке соответствий
        $this->redirect_to_index_action('mapping', "&object_id={$object_id}&phase=1");
        return true;
    }

    /**
     * Настройка автоматизированного обмена с 1C/МойСклад
     *
     * @param $action
     * @param $site_id
     * @param int $object_id
     * @param int $phase
     * @return nc_ui_view
     * @throws nc_netshop_exchange_exception
     */
    public function action_setup_automated_cml($action, $site_id, $object_id = -1, $phase = 1) {
        $view = $this->get_view($action, $site_id, $object_id, $phase);
        $view->step = NETCAT_MODULE_NETSHOP_EXCHANGE_STEP_CML;
        $view->next_action = 'setup_automated_cml_save';

        $object = nc_netshop_exchange_import::by_id($object_id);
        $view->has_files = $object->has_acceptable_files();

        // Логин и пароль для 1C
        $catalogue_id = $object->get_catalogue_id();
        $nc_netshop = nc_netshop::get_instance($catalogue_id);
        $secret_name = $nc_netshop->get_setting('1cSecretName');
        $secret_key = $nc_netshop->get_setting('1cSecretKey');

        $view->object = $object;
        $view->secret_name = $secret_name;
        $view->secret_key = $secret_key;

        $this->add_back_button();
        $this->add_next_button();

        return $view;
    }

    /**
     * Сохранение автоматизированного обмена с 1С/МойСклад
     *
     * @param int $object_id
     * @throws nc_netshop_exchange_exception
     */
    public function action_setup_automated_cml_save($object_id = -1) {
        // Соберем данные по импорту, чтобы знать что нам предстоит маппить
        $object = nc_netshop_exchange_import::by_id($object_id);
        $object->get_mapping()->start();
        $this->redirect_to_index_action('mapping', "&object_id={$object_id}&phase=1");
    }

    /**
     * Выбор объектов (файлов/разделов/листов) для маппинга
     *
     * @param $action
     * @param $site_id
     * @param int $object_id
     * @param int $phase
     * @return nc_ui_view
     * @throws nc_netshop_exchange_exception
     */
    public function action_selection($action, $site_id, $object_id = -1, $phase = 1) {
        $view = $this->get_view($action, $site_id, $object_id, $phase);
        $view->step = NETCAT_MODULE_NETSHOP_EXCHANGE_STEP_SELECTION;
        $view->next_action = 'selection_save';

        // Объекты маппинга
        $object = nc_netshop_exchange_import::by_id($object_id);
        $items = $object->get_mapping()->get_all();
        $object_exchange_mapping = json_decode($object->get('exchange_mapping'), true);
        $has_checked_items = false;
        foreach ((array)$items as $i => $item_key) {
            $checked = !array_key_exists($item_key, $object_exchange_mapping);
            $items[$i] = array(
                'checked' => $checked,
                'mapped' => !$checked,
                'text' => $object->item_key_info($item_key),
            );
            if ($checked) {
                $has_checked_items = true;
            }
        }

        // Если новых не было - выберем все объекты
        if (!$has_checked_items) {
            foreach ($items as $i => $item_key) {
                $items[$i]['checked'] = true;
            }
        }

        $view->object = $object;
        $view->items = $items;

        $this->add_back_button();
        $this->add_next_button();

        return $view;
    }

    /**
     * Сохранение (создание) объекта обмена
     * @param int $object_id
     * @throws nc_netshop_exchange_exception
     */
    public function action_selection_save($object_id = -1) {
        $nc_core = nc_core::get_object();

        // Если есть невыбранные элементы, то удалим их
        $selected_items_indexes = $nc_core->input->fetch_post('item_index');
        $object = nc_netshop_exchange_import::by_id($object_id);
        $items = $object->get_mapping()->get_all();
        $items_count = count($items);
        if (count($selected_items_indexes) < $items_count) {
            for ($i = 0; $i < $items_count; $i++) {
                if (in_array($i, $selected_items_indexes)) {
                    continue;
                }
                unset($items[$i]);
            }
            $object->get_mapping()->set_all($items);
        }

        // Перейдем к настройке соответствий
        $this->redirect_to_index_action('mapping', "&object_id={$object_id}&phase=1");
    }

    /**
     * Маппинг (настройка разделов, компонентов и их полей)
     * @param $action
     * @param $site_id
     * @param int $object_id
     * @param int $phase
     * @return nc_ui_view
     */
    public function action_mapping($action, $site_id, $object_id = -1, $phase = 1) {
        $view = $this->get_view($action, $site_id, $object_id, $phase);

        $view->step = NETCAT_MODULE_NETSHOP_EXCHANGE_STEP_MAPPING;
        $view->next_action = 'mapping_save';

        $db = nc_db();

        $object = nc_netshop_exchange_import::by_id($object_id);
        $view->object = $object;

        // Есть ли файлы для настройки маппинга?
        $has_files = $object->has_acceptable_files();
        $view->has_files = $has_files;

        // Загружены ли файлы?
        $view->just_uploaded = nc_netshop_exchange_session::get('just_uploaded');
        nc_netshop_exchange_session::delete('just_uploaded');

        // Подготовим всё для маппинга
        if ($has_files) {
            $this->add_next_button();
        }

        $item_key = $object->get_mapping()->current();
        $view->item_key_info = $object->item_key_info($item_key);
        $item_key_data = nc_netshop_exchange_helper::item_key_to_data($item_key);
        $file_path = $item_key_data['file_path'];
        $scope = $item_key_data['scope'];
        $scope_name = $item_key_data['scope_name'];

        $object_exchange_mapping = json_decode($object->get('exchange_mapping'), true);

        // Распарсим файл
        $file_data = $object->get_data($file_path, $scope);

        $has_goods = !empty($file_data['goods']);
        $view->has_goods = $has_goods;
        $skip_rows = $object->always_has_caption() ? 1 : 0;

        // Список компонентов (предварительно модифицируем названия полей)
        $components = $object->get_components();

        $data_preview = null;
        if ($has_goods) {
            $data_preview = array_slice($file_data['goods'], 0, 3);
            $view->preview = $data_preview;
            $view->fields_keys = $object->get_data_fields_keys($file_data['goods']);
            $view->fields_types = nc_netshop_exchange_helper::get_fields_types();
            $skip_rows = nc_array_value($object_exchange_mapping[$item_key], 'skip_rows', $skip_rows);
            $components_processed = $components;
            foreach ($components_processed as $component_id => &$component_data) {
                // Преобразуем ассоциативный массив в обычный, иначе в javascript поля перемешаются
                $tmp_arr = array();
                foreach ($component_data['fields'] as $key => $val) {
                    $tmp_arr[] = array(
                        'id' => $key,
                        'field' => $val,
                    );
                }
                $component_data['fields'] = $tmp_arr;
                unset($field);
            }
            unset($component_data);
            $view->components = $components_processed;
        }
        $view->skip_rows = $skip_rows;
        $view->show_skip_rows = !$object->always_has_caption();

        // Разделы
        $is_subdivision_exists = false;
        $default_subdivision_id = 0;
        // Если сопоставление для раздела найдено - выберем его
        if (!empty($object_exchange_mapping[$item_key]['subdivision_id'])) {
            $default_subdivision_id = $object_exchange_mapping[$item_key]['subdivision_id'];
            $is_subdivision_exists = true;
        }
        // Если сопоставление для раздела не выбрано - то попробуем определить, может быть можно выбрать родитеский
        // раздел на основе одного из предыдущих шагов маппинга
        if (empty($default_subdivision_id)) {
            $parent_subdivision_id_in_file = $file_data['subdivision_parent_id'];
            if ($parent_subdivision_id_in_file) {
                foreach ($object_exchange_mapping as $already_item_key => $already_data) {
                    $already_item_key_data = nc_netshop_exchange_helper::item_key_to_data($already_item_key);
                    if ($parent_subdivision_id_in_file == $already_item_key_data['scope']) {
                        $default_subdivision_id = $already_data['subdivision_id'];
                        break;
                    }
                }
            }
        }
        // Если сопоставление для раздела до сих пор не выбрано - то попробуем найти соседний раздел для данного,
        // и выбрать его родителя как родителя для текущего раздела
        if (empty($default_subdivision_id)) {
            // Уровень вложенности нового раздела
            $new_subdivision_level = substr_count($scope_name, ' / ');
            if (!empty($object_exchange_mapping)) {
                foreach ($object_exchange_mapping as $already_item_key => $already_data) {
                    $already_item_key_data = nc_netshop_exchange_helper::item_key_to_data($already_item_key);
                    $already_subdivision_level_count = substr_count($already_item_key_data['scope_name'], ' / ');
                    if ($new_subdivision_level == $already_subdivision_level_count) {
                        $already_subdivision_id = $already_data['subdivision_id'];
                        if (!empty($already_subdivision_id)) {
                            $already_subdivision_parent_id = $db->get_var("SELECT `Parent_Sub_ID` FROM `Subdivision` WHERE `Subdivision_ID`='{$already_subdivision_id}'");
                            $default_subdivision_id = !empty($already_subdivision_parent_id) ? $already_subdivision_parent_id : $already_subdivision_id;
                        }
                        break;
                    }
                }
            }
        }
        // Если сопоставление для раздела до сих пор не выбрано - то выберем раздел с предыдущего шага маппинга
        // (если такой есть) в качестве родительского
        if (empty($default_subdivision_id)) {
            if (!empty($object_exchange_mapping)) {
                $last_item_key = array_keys($object_exchange_mapping);
                $last_item_key = $last_item_key[count($last_item_key) - 1];
                $last_item = $object_exchange_mapping[$last_item_key];
                if (!empty($last_item['subdivision_id'])) {
                    $default_subdivision_id = $last_item['subdivision_id'];
                }
            }
        }
        $new_subdivision_possible_name = !empty($file_data['subdivision_name']) ? $file_data['subdivision_name'] : null;
        $new_subdivision_possible_name = nc_netshop_exchange_helper::get_subdivision_possible_name($new_subdivision_possible_name, $default_subdivision_id);
        $view->new_subdivision_possible_name = $new_subdivision_possible_name;
        $new_subdivision_possible_keyword = nc_netshop_exchange_helper::get_subdivision_possible_keyword($new_subdivision_possible_name, $default_subdivision_id);
        $view->new_subdivision_possible_keyword = $new_subdivision_possible_keyword;
        $subdivisions = nc_netshop_exchange_helper::get_subdivisions($object->get('catalogue_id'));
        $view->subdivisions = nc_netshop_exchange_helper::print_tree_as_select($subdivisions, 'mapping[subdivision_id]', $default_subdivision_id);
        $view->is_subdivision_exists = $is_subdivision_exists;

        // Компонент
        $new_component_possible_name = !empty($file_data['subdivision_name']) ? $file_data['subdivision_name'] : null;
        $new_component_possible_name = nc_netshop_exchange_helper::get_component_possible_name($new_component_possible_name);
        $view->new_component_possible_name = $new_component_possible_name;
        $new_component_possible_keyword = nc_netshop_exchange_helper::get_component_possible_keyword($new_component_possible_name);
        $view->new_component_possible_keyword = $new_component_possible_keyword;

        $data_captions = !empty($data_preview[0]) ? $data_preview[0] : null;
        $object->extend_hard_mapped_fields($components, $object_exchange_mapping, $data_captions);
        $is_component_exists = false;
        $default_component_id = 0;
        // Id первого компонента, который выберется скриптом по умолчанию
        $first_component_id = array_keys($components);
        $first_component_id = $first_component_id[0];
        if (!empty($object_exchange_mapping[$item_key]['component_id'])) {
            $default_component_id = $object_exchange_mapping[$item_key]['component_id'];
            $is_component_exists = true;
        } else {
            // Попытаемся найти используемый компонент, помимо стандартного.
            // Поищем смежные уже настроенные категории (например, если текущая "каталог / мужская одежка / штаны", то
            // можно поискать какой компонент используется в категории "каталог / мужская одежда / джинсы")
            $parent_scope = explode(' / ', $scope_name);
            if (count($parent_scope) > 1) {
                array_pop($parent_scope);
                foreach ($object_exchange_mapping as $already_item_key => $already_data) {
                    $already_item_key_data = nc_netshop_exchange_helper::item_key_to_data($already_item_key);
                    $already_item_parent_scope = explode(' / ', $already_item_key_data['scope_name']);
                    if (count($already_item_parent_scope) == 1) {
                        continue;
                    }
                    array_pop($already_item_parent_scope);
                    // Если массивы равны - то это будет значить, что текущая настраиваемая категория и категория из
                    // ранее настроенных - лежат в одном и том же разделе
                    if ($parent_scope == $already_item_parent_scope) {
                        // Попробуем взять компонент из этого раздела
                        $already_item_component_id = $already_data['component_id'];
                        if (!empty($already_item_component_id) && $already_item_component_id != $first_component_id) {
                            $default_component_id = $already_item_component_id;
                            $is_component_exists = true;
                            break;
                        }
                    }
                }
            }
            // Если всё ещё не найден - попробуем найти любой используемый компонент, кроме стандартного
            if (empty($default_component_id) && !empty($object_exchange_mapping)) {
                foreach ($object_exchange_mapping as $already_item_key => $already_data) {
                    $already_item_component_id = $already_data['component_id'];
                    if (!empty($already_item_component_id) && $already_item_component_id != $first_component_id) {
                        $default_component_id = $already_item_component_id;
                        $is_component_exists = true;
                        break;
                    }
                }
            }
        }
        if ($object['format'] == nc_netshop_exchange_import::FORMAT_PRICE) {
            $default_component_id = $first_component_id;
            $is_component_exists = true;
        }
        $view->is_component_exists = $is_component_exists;
        $view->default_component_id = $default_component_id;

        // Настроим вьюшку и шаги
        $view->mapping = !empty($object_exchange_mapping[$item_key]) ? $object_exchange_mapping[$item_key] : null;
        $view->item_key = $item_key;
        $view->file_name = pathinfo($file_path, PATHINFO_BASENAME);
        $view->scope_name = $scope_name;
        $view->phases = $object->get_mapping()->total();
        $view->phase = $object->get_mapping()->index();

        return $view;
    }

    /**
     * Сохранение данных маппинга
     * @param int $object_id
     * @param int $phase
     */
    public function action_mapping_save($object_id = -1, $phase = 1) {
        $nc_core = nc_core::get_object();
        $object = nc_netshop_exchange_import::by_id($object_id);

        $db = nc_db();

        $object_exchange_mapping = json_decode($object->get('exchange_mapping'), true);

        $mapping_data = $this->input->fetch_post('mapping');

        // Создание нового компонента
        if (!$mapping_data['only_subdivision']) {
            if ($mapping_data['component_source'] === 'new') {
                $fields = array();

                foreach ($mapping_data as $key => $val) {
                    if (substr($key, 0, 6) === 'field_') {
                        // field_10_Name => Field 10 Name
                        list(, $index, $characteristic) = explode('_', $key);

                        // Новое поле - запомним индекс
                        if (empty($characteristic) && $val == -1) {
                            if (!array_key_exists($index, $fields)) {
                                $fields[$index] = array();
                            }
                        }

                        if (!empty($characteristic) && array_key_exists($index, $fields)) {
                            $fields[$index][$characteristic] = $val;
                        }
                    }
                }

                // Создадим компонент
                $old_component_id = $mapping_data['component_id'];
                $mapping_data['component_id'] = $nc_core->component->create(array(
                    'Keyword' => nc_netshop_exchange_helper::get_component_possible_keyword($mapping_data['component_keyword']),
                    'Class_Name' => $mapping_data['component_name'],
                ), $mapping_data['component_id']);

                // Создадим поля для него
                if (!empty($fields)) {
                    foreach ($fields as $field_index => $field) {
                        $mapping_data['field_' . $field_index] = $nc_core->component->add_field($mapping_data['component_id'], array(
                            'Field_Name' => $field['name'],
                            'Description' => $field['description'],
                            'TypeOfData_ID' => $field['type'],
                            'Format' => $field['format'],
                        ));
                    }
                }

                // Теперь обновим ID полей, т.к. мы создали компонент, а привязка у нас идет к Id полей для базового компонента.
                $field_index = 0;
                while (array_key_exists('field_' . $field_index, $mapping_data)) {
                    // Id поля в базовом компоненте
                    $field_id = $mapping_data['field_' . $field_index];

                    if ($field_id <= 0) {
                        $field_index++;
                        continue;
                    }

                    // Название поля и в базовом, и в новом компонентах
                    $field_name = $this->core->db->get_var("SELECT `Field_Name` FROM `Field` WHERE `Class_ID`='{$old_component_id}' AND `Field_ID`='{$field_id}'");

                    if (empty($field_name)) {
                        $field_index++;
                        continue;
                    }

                    // Найдем новый Id поля
                    $new_component_id = $mapping_data['component_id'];
                    $field_id = $this->core->db->get_var("SELECT `Field_ID` FROM `Field` WHERE `Class_ID`='{$new_component_id}' AND `Field_Name`='{$field_name}'");

                    // Предусмотрим случай, если это новое поле в новом компоненте, и его нет в родительском компоненте
                    if (empty($field_id)) {
                        $field_index++;
                        continue;
                    }

                    $mapping_data['field_' . $field_index] = $field_id;

                    $field_index++;
                }
            }
        }

        // Создание нового раздела и инфоблока
        if ($mapping_data['subdivision_source'] === 'new') {
            // Создадим раздел
            $mapping_data['subdivision_id'] = $nc_core->subdivision->create(array(
                'Catalogue_ID' => $this->site_id,
                'Parent_Sub_ID' => $mapping_data['subdivision_id'],
                'Subdivision_Name' => $mapping_data['subdivision_name'],
                'EnglishName' => nc_netshop_exchange_helper::get_subdivision_possible_keyword($mapping_data['subdivision_english_name']),
            ));

            // Если на текущем шаге мастера обмена сгенерирован только раздел - то необходимо прикрепить
            // к созданному разделу компонент для переадресации на первый дочерний раздел
            if ($mapping_data['only_subdivision']) {
                $redirect_component_id = nc_netshop_exchange_helper::get_redirect_component_id();
                if ($redirect_component_id) {
                    $nc_core->sub_class->create($redirect_component_id, array(
                        'Subdivision_ID' => $mapping_data['subdivision_id'],
                    ));
                }
            }
        }

        // Проверим, есть ли у данного раздела инфоблок для данного компонента
        if (!$mapping_data['only_subdivision'] && $object['format'] != nc_netshop_exchange_import::FORMAT_PRICE) {
            $subdivision_id = $mapping_data['subdivision_id'];
            $component_id = $mapping_data['component_id'];
            $sub_class_id = $this->core->db->get_var("SELECT `Sub_Class_ID` FROM `Sub_Class` WHERE `Subdivision_ID`='{$subdivision_id}' AND `Class_ID`='{$component_id}'");
            if (empty($sub_class_id)) {
                // Добавим инфоблок в данный раздел для данного компонента
                $nc_core->sub_class->create($component_id, array(
                    'Subdivision_ID' => $subdivision_id,
                    'EnglishName' => nc_netshop_exchange_helper::get_sub_class_possible_keyword($mapping_data['component_name'], $subdivision_id),
                ));
            }
        }

        // Заполним массив маппинга
        $object_exchange_mapping[$mapping_data['item_key']] = array(
            'subdivision_id' => $mapping_data['subdivision_id'],
            'component_id' => !empty($mapping_data['component_id']) ? $mapping_data['component_id'] : null,
            'fields' => array(),
        );

        if (!$mapping_data['only_subdivision']) {
            $fields_data = array();
            foreach ($mapping_data as $key => $val) {
                if (substr($key, 0, 6) !== 'field_') {
                    continue;
                }

                // field_10_Name => Field 10 Name
                list(, $field_number, $characteristic) = explode('_', $key);

                if (empty($characteristic)) {
                    $characteristic = 'field_id';
                }

                if (!array_key_exists($field_number, $fields_data)) {
                    $fields_data[$field_number] = array();
                }
                $fields_data[$field_number][$characteristic] = $val;
            }

            foreach ($fields_data as $index => $field_data) {
                if ($field_data['field_id'] == 0) {
                    continue;
                }

                $object_exchange_mapping[$mapping_data['item_key']]['fields'][$field_data['key']] = $field_data['field_id'];
            }
        }

        // Заполним массив настроек
        if (!$mapping_data['only_subdivision']) {
            $skip_rows = (int)nc_array_value($mapping_data, 'skip_rows', 0);
            $skip_rows  = $skip_rows >= 0 ? $skip_rows : 0;
            $object_exchange_mapping[$mapping_data['item_key']]['skip_rows'] = $skip_rows;
        }

        // Сохраним
        $object->set('exchange_mapping', nc_array_json($object_exchange_mapping));
        $object->save();

        $component_id = $object_exchange_mapping[$mapping_data['item_key']]['component_id'];
        if (!empty($component_id) && $mapping_data['component_source'] === 'new') {
            // Обновим у компонента поля, определяющие варианты
            $variants_data = $this->input->fetch_post('variant');
            if ($variants_data['variants_source'] === 'new') {
                // Поля, которые будут вариантами
                $fields = $variants_data['fields'];

                if (!empty($fields)) {
                    // Путь к файлу Settings у выбранного компонента
                    $component_data = $nc_core->component->get_by_id($component_id);
                    $component_folder_path = (rtrim($this->core->CLASS_TEMPLATE_FOLDER, '/')) . $component_data['File_Path'];
                    $component_settings_file_path = $component_folder_path . 'Settings.html';

                    $file_content = file_get_contents($component_settings_file_path);

                    // Найдем старый PHP массив, задающий настройки вариантов товаров
                    preg_match_all('/\$variant_fields\s*=\s*array\((.*?)\);/ism', $file_content, $matches, PREG_SET_ORDER, 0);

                    if (!empty($matches[0][0])) {
                        $php_old_array = $matches[0][0];
                    }

                    // Заменим новыми настройками
                    $php_new_array = array('$variant_fields = array(');
                    $class_id = $mapping_data['component_id'];
                    foreach ($fields as $field) {
                        $field_description = $db->get_var("SELECT `Description` FROM `Field` WHERE `Class_ID`='{$class_id}' AND `Field_Name`='{$field}'");
                        $field_call = NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT. ' "' . $field_description . '"';
                        $php_new_array[] = "    '{$field}' => array('caption' => '{$field_description}', 'placeholder' => '{$field_call}'),";
                    }
                    $php_new_array[] = ");";
                    $php_new_array = implode("\r\n", $php_new_array);

                    // Добавим или обновим массив
                    if (empty($php_old_array)) {
                        $file_content .= $php_new_array;
                    } else {
                        $file_content = str_replace($php_old_array, $php_new_array, $file_content);
                    }

                    file_put_contents($component_settings_file_path, $file_content);
                }
            }

            // Обновим у компонента поля, определяющие поля для поиска
            $search_data = $this->input->fetch_post('search');
            if ($search_data['search_source'] === 'new') {
                // Поля, которые будут поисковые поля
                $fields = $search_data['fields'];

                if (!empty($fields)) {
                    // Путь к файлу Settings у выбранного компонента
                    $component_data = $nc_core->component->get_by_id($component_id);
                    $component_folder_path = rtrim($this->core->CLASS_TEMPLATE_FOLDER, '/') . $component_data['File_Path'];
                    $component_settings_file_path = $component_folder_path . 'Settings.html';

                    $file_content = file_get_contents($component_settings_file_path);

                    // Найдем старый PHP массив, задающий настройки
                    preg_match_all('/\$filter_fields\s*=\s*array\((.*?)\);/ism', $file_content, $matches, PREG_SET_ORDER, 0);

                    if (!empty($matches[0][0])) {
                        $php_old_array = $matches[0][0];
                    }

                    // Заменим новыми настройками
                    $php_new_array = array('$filter_fields = array(');
                    foreach ($fields as $field) {
                        $php_new_array[] = "    '{$field}',";
                    }
                    $php_new_array[] = ");";
                    $php_new_array = implode("\r\n", $php_new_array);

                    // Добавим или обновим массив
                    if (empty($php_old_array)) {
                        $file_content .= $php_new_array;
                    } else {
                        $file_content = str_replace($php_old_array, $php_new_array, $file_content);
                    }

                    file_put_contents($component_settings_file_path, $file_content);
                }
            }
        }

        $object->get_mapping()->next();
        // Если замаппили все файлы
        $phase++;
        if ($phase <= $object->get_mapping()->total()) {
            $this->redirect_to_index_action('mapping', "&object_id={$object_id}&phase={$phase}");
        } else {
            $object->get_mapping()->finish();
            $this->redirect_to_index_action('config', "&object_id={$object_id}&phase=1");
        }
    }

    /**
     * Дальнейшая настройка объекта обмена
     * @param $action
     * @param $site_id
     * @param int $object_id
     * @param int $phase
     * @return nc_ui_view
     */
    public function action_config($action, $site_id, $object_id = -1, $phase = 1) {
        $view = $this->get_view($action, $site_id, $object_id, $phase);

        $view->step = NETCAT_MODULE_NETSHOP_EXCHANGE_STEP_FINISH;
        $view->next_action = 'config_save';

        $object = nc_netshop_exchange_object::by_id($object_id);
        $view->object = $object;
        $view->warnings = $object->get_environment_warnings();

        $this->add_next_button(NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_RUN_EXCHANGE);

        return $view;
    }

    /**
     * Сохранение настроек обмена
     * @param int $object_id
     * @return null
     */
    public function action_config_save($object_id = -1) {
        $data = $this->core->input->fetch_post('exchange');

        $object = nc_netshop_exchange_object::by_id($object_id);

        $object_name = !empty($data['save']) && !empty($data['name']) ? $data['name'] : '';
        $object->set('name', $object_name);

        $object_email = !empty($data['do_email']) && !empty($data['email']) ? $data['email'] : '';
        $object->set('email', $object_email);

        $object->save();

        $this->redirect_to_index_action('report', "&object_id={$object_id}&phase=1");
        return null;
    }

    /**
     * Запуск обмена и вывод отчета
     * @param $action
     * @param $site_id
     * @param int $object_id
     * @param int $phase
     * @return nc_ui_view
     */
    public function action_report($action, $site_id, $object_id = -1, $phase = 1) {
        $view = $this->get_view($action, $site_id, $object_id, $phase);

        $view->step = NETCAT_MODULE_NETSHOP_EXCHANGE_REPORT;
        $view->next_controller = 'exchange';
        $view->next_action = 'index';

        $object = nc_netshop_exchange_import::by_id($object_id);
        $exchange_id = $object->run();
        $log = new nc_netshop_exchange_log($object);

        $view->report = $log->build_report($exchange_id);

        // Если мы не напрямую попали в этот экшн
        if ($phase != 2) {
            // Обработка нового объекта (созданного в рамках данного мастера)
            if (!$object->is_checked()) {
                // Если объект имеет имя - его нужно активировать, иначе - удалить
                if ($object->get('name')) {
                    $object->set('checked', true);
                    if ($object->get_mode() == nc_netshop_exchange_object::MODE_AUTOMATED) {
                        $object->set('automated_mode_enabled', true);
                    }
                    $object->save();
                } else {
                    $object->delete();
                }
            }
        }

        $this->add_next_button(NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_FINISH);

        return $view;
    }
}