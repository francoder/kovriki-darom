<?php

class nc_image_path_field extends nc_image_path {

    protected $infoblock_id = null;
    protected static $counter = 0;

    /**
     * Проверка на возможность редактирования пользователем изображений
     * @return bool
     * @throws Exception
     */
    protected function can_user_edit_image() {
        /** @var Permission $perm */
        $perm = null;
        if ($GLOBALS['perm'] instanceof Permission) {
            $perm = $GLOBALS['perm'];
        }
        if (!$perm) {
            return false;
        }
        $cc = $this->get_infoblock_id();
        if (!$cc) {
            return false;
        }
        return $perm->isSubClass($cc, MASK_EDIT);
    }

    /**
     * Возвращает ID инфоблока, на основе ID компонента и ID объекта
     * @return null|integer
     */
    protected function get_infoblock_id() {
        if (!is_null($this->infoblock_id)) {
            return $this->infoblock_id;
        }
        $component_id = (int)$this->entity;
        $object_id = (int)$this->object;
        $nc_core = nc_core::get_object();
        $this->infoblock_id =  $nc_core->db->get_var(
            "SELECT `Sub_Class_ID`
            FROM `Message{$component_id}`
            WHERE `Message_ID`='{$object_id}'"
        );
        return $this->infoblock_id;
    }

    /**
     * Генерирует форму редактирования изображения
     * @return string
     * @throws Exception
     */
    protected function get_editable_image_form($file_path) {
        $nc_core = nc_Core::get_object();
        $attributes = $this->tag_attributes;
        $attributes = (is_array($attributes)) ? $attributes : array();
        $component_id = (int)$this->entity;
        $field_id = (int)$this->field;
        $object_id = (int)$this->object;
        if (!$component_id || !$field_id || !$object_id) {
            return $this->get_img_tag();
        }
        $infoblock_id = $this->get_infoblock_id();
        if (!$infoblock_id) {
            return $this->get_img_tag();
        }
        $for_v4 = !$nc_core->sub_class->get_by_id($infoblock_id, 'File_Mode');
        $field = $nc_core->get_component($component_id)->get_field($field_id);
        if (!$field) {
            return $this->get_img_tag();
        }
        $field_name = $field['name'];
        $file_info = $nc_core->file_info->get_file_info($component_id, $object_id, $field_name, false);
        $is_file_present = !empty($file_info["url"]);
        $root_class = 'nc-editable-image-container';
        if (!$is_file_present) {
            $root_class .= ' nc--empty';
        }
        $root_id = "nc_editable_image_{$component_id}_{$field_name}_{$object_id}_" . (self::$counter++);
        $html = "<span class='$root_class' id='$root_id'>";
        $image_placeholder = $nc_core->ADMIN_PATH . 'skins/v5/img/transparent-100x100.png';
        $file_path = $is_file_present ? $this->get_path() : $image_placeholder;
        if ($nc_core->get_settings("InlineImageCropUse") == 1) {
            // нажатие на кнопку открывает диалог «Редактирование изображений»
            $attributes['src'] = $file_path;
            if (isset($attributes['class'])) {
                $attributes['class'] .= ' cropable' . ($is_file_present ? '' : ' nc--placeholder');
            } else {
                $attributes['class'] = 'cropable' . ($is_file_present ? '' : ' nc--placeholder');
            }
            $attributes['data-classid'] = $component_id;
            $attributes['data-messageid'] = $object_id;
            $attributes['data-fieldname'] = $field_name;
            $processed_attributes = nc_make_attribute_string_from_array($attributes, $for_v4);
            $html .= $processed_attributes['warning'];
            $html .= '<img ' . $processed_attributes['result'] . ' />';
        }
        else {
            // нажатие на кнопку должно открыть диалог выбора файла
            // если картинка не является обязательной, показываем тулбар с кнопкой удаления картинки
            if (!$field['not_null']) {
                $html .= "<span class='nc-editable-image-container-toolbar-bridge'></span>" .
                    "<ul class='nc6-toolbar nc-editable-image-container-toolbar'>" .
                    "<li class='nc--strike-diagonal nc-editable-image-remove'>".
                    "<i class='nc-icon-image' title='" . NETCAT_MODERATION_REMOVE_IMAGE . "'></i></li>" .
                    "</ul>";
            }
            $form_action = $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH . 'message.php';
            $html .= "<form action='$form_action' method='post' enctype='multipart/form-data' onsubmit='return false'>" .
                $nc_core->token->get_input() .
                "<input name='posting' type='hidden' value='1'>" .
                "<input name='partial' type='hidden' value='1'>" .
                "<input name='admin_mode' type='hidden' value='1'>" .
                "<input name='isNaked' type='hidden' value='1'>" .
                "<input name='cc' type='hidden' value='$infoblock_id'>" .
                "<input name='message' type='hidden' value='$object_id'>" .
                "<input name='f_KILL{$field['id']}' type='hidden' value=''>";

            $nc_image_attributes['src'] = $file_path;
            $processed_attributes = nc_make_attribute_string_from_array($nc_image_attributes, $for_v4);
            $html .= $processed_attributes['warning'];
            $html .= '<img ' . $processed_attributes['result'] . ' />';
            $html .= "<span class='nc-editable-image-container-file-input'>" .
                "<input type='file' name='f_{$field_name}' accept='image/*'" .
                " title='" . NETCAT_MODERATION_REPLACE_IMAGE . "'>" .
                "</span>";
            $html .= "</form>";
        }
        $html .= "</span>";
        $html .= "<script>nc_editable_image_init('#$root_id')</script>";
        return $html;
    }

}