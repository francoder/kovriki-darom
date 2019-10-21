<?php

class nc_netshop_exchange_admin_controller extends nc_netshop_admin_controller {
    /** @var nc_netshop_exchange_admin_ui */
    protected $ui_config;
    protected $ui_config_class = 'nc_netshop_exchange_admin_ui';
    protected $ui_config_base_path = 'exchange';

    protected function init() {
        parent::init();
        
        $this->bind('edit', array('id'));
        $this->bind('logs', array('id', 'exchange_id', 'direct_open'));
        $this->bind('change_priority', array('id', 'priority'));
    }

    /**
     * Вывод объектов обмена
     * @return nc_ui_view
     */
    public function action_index() {
        $table = new nc_netshop_exchange_table();
        $objects = $table->for_site($this->site_id)->where('Checked', true)->order_by('Exchange_ID')->as_array()->get_result();

        $view = $this->view('object_list');
        $view->objects = $objects;
        
        $this->ui_config->set_exchange_location_suffix("({$this->site_id})");
        $this->ui_config->add_create_button("exchange.wizard(start,{$this->site_id},-1,1)");

        // Найдём ненастроенный объект обмена (если есть)
        $not_finished_object = $table->select('Exchange_ID')->for_site($this->site_id)->where('Checked', false)->as_array()->get_result();
        $not_finished_object = !empty($not_finished_object) ? $not_finished_object[0]['Exchange_ID'] : null;
        if (!empty($not_finished_object)) {
            $not_finished_object = nc_netshop_exchange_object::by_id($not_finished_object);
        }
        $view->not_finished_object = $not_finished_object;

        return $view;
    }

    /**
     * Сохранение объекта обмена
     * @return nc_ui_view
     */
    protected function action_save() {
        // Сохраним данные
        $object_data = $this->input->fetch_post('object');
        $object = nc_netshop_exchange_object::from_array($object_data);
        $object->save();

        $this->redirect_to_index_action();
        return null;
    }
    
    /**
     * Удаление объекта обмена
     * @return nc_ui_view
     */
    protected function action_remove() {
        $nc_core = nc_core::get_object();
        $id = (int)$nc_core->input->fetch_post('id');
        if ($id) {
            // Удалим объект
            try {
                $object = nc_netshop_exchange_object::by_id($id);
                $object->delete();
            }
            catch (Exception $e) {}
        }
        $this->redirect_to_index_action();
        return null;
    }

    /**
     * Добавление ошибки в ответ сервера
     *
     * @param array $response - ответ
     * @param string $error - ошибка
     */
    private function response_add_error(&$response, $error) {
        $response['status'] = 'error';
        $response['errors'][] = $error;
    }

    /**
     * Проверка возможности получения доступа для использования остальных методов
     * @param nc_core $nc_core
     * @param array $response
     */
    private function check_access_rights($nc_core, $response) {
        // Проверка токена
        if ($nc_core->token->verify()) {
            return;
        }
        $this->response_add_error($response, 'you have no access rights');
        $this->send_response($response);
    }

    /**
     * Отсылает ответ
     * @param array $response
     */
    private function send_response($response) {
        // Отдаем ответ
        header('Content-Type: application/json; charset=utf-8');
        exit(nc_array_json($response));
    }

    /**
     * Проверка данных при маппинге.
     * Проверяет разделы и компоненты, их ключевые слова, а также корректность новых создаваемых полей.
     */
    protected function action_check_mapping_data() {
        $nc_core = nc_core::get_object();
        $nc_input = $nc_core->input;
        $nc_db = $nc_core->db;

        $response = array(
            'status' => 'ok',
            'errors' => array(),
        );

        $this->check_access_rights($nc_core, $response);

        // Нужно ли проверять раздел?
        $sub_check = $nc_input->fetch_post_get('subdivision_check');
        if ($sub_check) {
            // Данные нового раздела
            $sub_base_id = (int)$nc_input->fetch_post_get('subdivision_base');
            $sub_name = $nc_input->fetch_post_get('subdivision_name');
            $sub_keyword = $nc_db->escape($nc_input->fetch_post_get('subdivision_keyword'));

            // Проверим, есть ли у родительского раздела подраздел с таким же keyword
            if ($sub_base_id >= 0 && !empty($sub_keyword)) {
                $sub_id = $nc_db->get_var("SELECT `Subdivision_ID` FROM `Subdivision` WHERE `Parent_Sub_ID`='{$sub_base_id}' AND `EnglishName`='{$sub_keyword}'");

                if ($sub_id) {
                    $this->response_add_error($response, NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_SECTION_HAS_CHILD_WITH_THIS_KEYWORD);
                }
            }

            // Проверим название раздела
            if (empty($sub_name)) {
                $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_SUBDIVISION_NAME));
            }

            // Проверим ключевое слово раздела;
            if (!$nc_core->subdivision->validate_english_name($sub_keyword)) {
                $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_WRONG_FORMAT, NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_SUBDIVISION_KEYWORD));
            }
        }

        // Нужно ли проверять компонент?
        $comp_check = $nc_input->fetch_post_get('component_check');
        if ($comp_check) {
            $comp_base_id = (int)$nc_db->escape($nc_input->fetch_post_get('component_base'));
            $comp_name = $nc_db->escape($nc_input->fetch_post_get('component_name'));
            $comp_keyword = $nc_db->escape($nc_input->fetch_post_get('component_keyword'));
            $comp_fields = $nc_input->fetch_post_get('component_fields');
            $comp_lists = $nc_input->fetch_post_get('component_lists');

            // Проверим название компонента
            if (empty($comp_name)) {
                $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_COMPONENT_NAME));
            }

            // Проверим ключевое слово компонента
            if (empty($comp_keyword)) {
                $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_COMPONENT_KEYWORD));
            } else {
                $comp_keyword_validation_result = $nc_core->component->validate_keyword($comp_keyword);
                if ($comp_keyword_validation_result !== true) {
                    $this->response_add_error($response, $comp_keyword_validation_result);
                }
            }

            // Проверим поля компонента
            if (!empty($comp_fields)) {
                // Стандартные поля
                $default_fields = array(
                    'Message_ID','User_ID','Subdivision_ID','Sub_Class_ID',
                    'Priority','Keyword','ncTitle','ncKeywords','ncDescription',
                    'ncSMO_Title','ncSMO_Description','ncSMO_Image','Checked','IP',
                    'UserAgent','Parent_Message_ID','Created','LastUpdated',
                    'LastUser_ID','LastIP','LastUserAgent'
                );

                foreach ($comp_fields as $field) {
                    if (in_array($field, $default_fields)) {
                        $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_NEW_FIELD_CANT_BE_NAMED_AS, $field));
                    }
                }

                // Проверим нестандартные поля
                array_filter($comp_fields, function(&$v) {
                    global $db;
                    $v = "'" . $db->escape($v) . "'";
                });
                $comp_fields = implode(',', $comp_fields);
                $fields = $nc_db->get_col("SELECT `Field_Name` FROM `Field` WHERE `Class_ID`='{$comp_base_id}' AND `Field_Name` IN ({$comp_fields})");
                if ($fields) {
                    $fields = implode(', ', $fields);
                    $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_BASE_COMPONENT_HAS_FIELDS_WITH_THIS_NAME, $fields));
                }
            }

            // Проверим списки, используемые в полях нового компонента
            if (!empty($comp_lists)) {
                foreach ($comp_lists as $list) {
                    list($list) = explode(':', $list);

                    $list_id = $nc_db->get_var("SELECT `Classificator_ID` FROM `Classificator` WHERE `Table_Name`='{$list}'");

                    if (empty($list_id)) {
                        $this->response_add_error($response, sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_HAS_NO_LIST_WITH_NAME, $list));
                    }
                }
            }
        }

        $this->send_response($response);
    }

    /**
     * Проверка раздела (если ли в нем товарный компонент).
     * Если есть - то возвращаем его id.
     */
    protected function action_check_subdivision_for_goods_component() {
        $nc_core = nc_core::get_object();
        $nc_input = $nc_core->input;
        $nc_db = $nc_core->db;

        $response = array(
            'status' => 'ok',
            'errors' => array(),
        );

        $this->check_access_rights($nc_core, $response);

        $subdivision_id = $nc_input->fetch_post_get('subdivision_id');
        $subdivision_id = $nc_db->escape($subdivision_id);
        if (empty($subdivision_id)) {
            $response['status'] = 'error';
        } else {
            $good_components_ids = nc_netshop_exchange_helper::get_goods_components_with_fields();
            $good_components_ids = array_keys($good_components_ids);
            $good_components_ids = implode(',', $good_components_ids);
            $component_id = $nc_db->get_var("SELECT `Class_ID` FROM `Sub_Class` WHERE `Subdivision_ID`='{$subdivision_id}' AND `Class_ID` IN ({$good_components_ids}) LIMIT 1");
            if (empty($component_id)) {
                $response['status'] = 'error';
            } else {
                $response['component_id'] = $component_id;
            }
        }

        $this->send_response($response);
    }
}
