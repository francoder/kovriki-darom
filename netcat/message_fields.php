<?php

if (!class_exists("nc_System")) {
    die("Unable to load file.");
}

$fldID = $fld = $fldName = $fldType = $fldFmt = $fldNotNull = $fldInheritance = $fldDefault = $fldTypeOfEdit = $fldDoSearch = array();
$format_string = array();

if ($user_table_mode) {
    $systemTableName = 'User';
}

if ($f_Keyword && $admin_mode) {
    $isDuplicatedKeyword = $db->get_var(
        "SELECT COUNT(*)
		 FROM `" . ($user_table_mode || $systemTableID ? 'User' : 'Message' . $classID) . "`
		 WHERE `Keyword` = '" . $f_Keyword . "'
		 " . ($user_table_mode || $systemTableID ? '' : " AND `Subdivision_ID` = '{$sub}'") . "
		 " . ($action === 'change' ? ' AND `' . ($user_table_mode || $systemTableID ? 'User' : 'Message') . "_ID` <> '{$message}'" : ''));

    if (!$isDuplicatedKeyword && !$user_table_mode) {
        $isDuplicatedKeyword = $db->get_var(
            "SELECT COUNT(*) FROM `Sub_Class` WHERE `EnglishName` = '{$f_Keyword}' AND `Subdivision_ID` = '{$sub}'"
        );
    }
} else {
    $isDuplicatedKeyword = false;
}

$sql_where = 'AND ' . ($systemTableID ? ' `System_Table_ID` = ' . $systemTableID : '`Class_ID` = ' . $classID);

$SQL = "SELECT `Field_ID`,
	`Field_Name`,
	`Description`,
	`TypeOfData_ID`,
	`Format`,
	`NotNull`,
	`Inheritance`,
	`DefaultState`,
	`TypeOfEdit_ID`,
	" . ($systemTableID ? "1 AS `DoSearch`" : "`DoSearch`") . "
	FROM `Field`
	WHERE `Checked` = 1 " . $sql_where . "
	ORDER BY `Priority`";

$res = $db->get_results($SQL, ARRAY_N);

$fldCount = $db->num_rows;

$i = 0;
// "старые" значения полей типа "файл". Нужно иметь возможность откатиться назад
// при posting = 0
$old_file_values = array();
// файлы, которые были помечены к удалению
$file_to_delete = array();

$multifile_field = array();
$multifile_field_id = array();
$multifile_warnText = '';

if (is_array($res)) {
    foreach ($res as $field) {
        if ($field[3] == NC_FIELDTYPE_MULTIFILE) {
            $multifile_field[] = $field;
            $multifile_field_id[] = $field[0];
        }
    }
}
if (isset($multifile_field[0])) {
    $SQL = 'SELECT Name,
                   Size,
                   Path,
                   Field_ID,
                   Message_ID,
                   Preview,
                   ID,
                   Priority
            FROM Multifield
            WHERE Field_ID IN (' . implode(', ', $multifile_field_id) . ')
            AND Message_ID = ' . +($systemTableID == 3 ? $UserID : $message) . ' AND Message_ID > 0
            ORDER BY `Priority`';
    $multifile_fields_data = (array) $db->get_results($SQL);

    foreach ($multifile_fields_data as $field) {
        ${'multifile_fields_data_array' . $field->Field_ID}[] = $field;
    }

    foreach ($multifile_field as $field) {
        ${'f_' . $field[1]} = new nc_multifield($field[1], $field[2], $field[4], $field[0]);
        if (isset(${'multifile_fields_data_array' . $field[0]})) {
            ${'f_' . $field[1]}->set_data(${'multifile_fields_data_array' . $field[0]});
        }
    }
}

if (isset($posting) && $posting == 1) {
    // добавляем информацию о поле ncSMO_Image, чтобы оно сохранялось
    // стандартными средствами при $posting = 1
    /** @var nc_core $nc_core */
    $nc_component = $nc_core->get_component($systemTableName ?: $classID);
    $nc_smo_image_field = $nc_component->get_smo_image_field();
    if ($nc_smo_image_field) {
        $res[] = array(
            $nc_smo_image_field['id'],             // `Field_ID`,
            $nc_smo_image_field['name'],           // `Field_Name`,
            $nc_smo_image_field['description'],    // `Description`,
            $nc_smo_image_field['type'],           // `TypeOfData_ID`,
            $nc_smo_image_field['format'],         // `Format`,
            $nc_smo_image_field['not_null'],       // `NotNull`,
            0,                                     // `Inheritance`,
            $nc_smo_image_field['default'],        // `DefaultState`,
            $nc_smo_image_field['edit_type'],      // `TypeOfEdit_ID`,
            $nc_smo_image_field['search'],         // `DoSearch`,
        );
        $fldCount++;
    }
}


if (!empty($res)) {
    foreach ($res AS $value) {
        // possibly there are no additional fields
        list($fldID[$i], $fld[$i], $fldName[$i], $fldType[$i], $fldFmt[$i], $fldNotNull[$i], $fldInheritance[$i], $fldDefault[$i], $fldTypeOfEdit[$i], $fldDoSearch[$i]) = $value;

        $fldValue[$i] = ${'f_' . $fld[$i]};

        $nc_field_requires_value = $fldNotNull[$i] && ($action === 'add' || !$partial);

        if (!in_array($fldType[$i], array(NC_FIELDTYPE_FILE, NC_FIELDTYPE_MULTIFILE, NC_FIELDTYPE_MULTISELECT))) {
            $fldValue[$i] = trim(stripslashes(${'f_' . $fld[$i]}));
        }

        switch ($fldType[$i]) {
            case NC_FIELDTYPE_BOOLEAN:
                if ($partial && !isset($_REQUEST["f_{$fld[$i]}"])) {
                    break;
                }

                # если "checkbox"
                if ($nc_field_requires_value && $fldValue[$i] == '') {
                    $fldValue[$i] = 0;
                }
                # если есть значение и оно не "1" и не "NULL", то 1
                if ($fldValue[$i] && !is_int($fldValue[$i]) && $fldValue[$i] !== 'NULL') {
                    $fldValue[$i] = 1;
                }
                # если значение "NULL" и по умолчанию "1"
                if ($fldDefault[$i] && ($fldValue[$i] === 'NULL' || $fldValue[$i] === NULL)) {
                    $fldValue[$i] = 1;
                }
                break;
            case NC_FIELDTYPE_DATETIME:
                if (
                    ${'f_' . $fld[$i] . '_year'} ||
                    ${'f_' . $fld[$i] . '_month'} ||
                    ${'f_' . $fld[$i] . '_day'} ||
                    ${'f_' . $fld[$i] . '_hours'} ||
                    ${'f_' . $fld[$i] . '_minutes'} ||
                    ${'f_' . $fld[$i] . '_seconds'}
                ) {
                    $fldValue[$i] = sprintf(
                        '%04d-%02d-%02d %02d:%02d:%02d',
                        ${'f_' . $fld[$i] . '_year'},
                        ${'f_' . $fld[$i] . '_month'},
                        ${'f_' . $fld[$i] . '_day'},
                        ${'f_' . $fld[$i] . '_hours'},
                        ${'f_' . $fld[$i] . '_minutes'},
                        ${'f_' . $fld[$i] . '_seconds'}
                    );
                }
                break;
            case NC_FIELDTYPE_MULTISELECT:
                $fldValue[$i] = ${'f_' . $fld[$i]};
                break;
        }

        $i++;
    }
}

# текст сообщений об ошибке
$errDescr[1] = NETCAT_MODERATION_MSG_ONE;
$errDescr[2] = NETCAT_MODERATION_MSG_TWO;
$errDescr[6] = NETCAT_MODERATION_MSG_SIX;
$errDescr[7] = NETCAT_MODERATION_MSG_SEVEN;
$errDescr[8] = NETCAT_MODERATION_MSG_EIGHT;
$errDescr[21] = NETCAT_MODERATION_MSG_TWENTYONE;

if ($isDuplicatedKeyword && $posting) {
    $posting = 0;
    $errCode = 21;
    $warnText = $errDescr[$errCode];
}

if (nc_strlen($f_Keyword) > 0 && $posting) {
    if (!nc_preg_match("/^[a-z" . NETCAT_RUALPHABET . "0-9\-_]+$/i", $f_Keyword)) {
        $posting = 0;
        $errCode = 21;
        $warnText = $errDescr[$errCode];
    }
}

if ($user_table_mode && $nc_core->modules->get_by_keyword('auth') && $action === 'add' && !$nc_core->inside_admin && $posting) {
    // самостоятельная регистрация запрещена
    if ($nc_core->get_settings('deny_reg', 'auth')) {
        $posting = 0;
        $warnText = NETCAT_MODULE_AUTH_SELFREG_DISABLED;
    }
    // пользовательское соглашение
    if (!$nc_agreed && $nc_core->get_settings('agreed', 'auth')) {
        $posting = 0;
        $warnText = NETCAT_MODERATION_MSG_NEED_AGREED . "<br/>";
    }
}


if ($user_table_mode && $posting && ($action === 'add' || (isset($Password1) && $action === 'change'))) {
    // совпадение паролей
    if ($Password1 !== $Password2 || !$Password1) {
        $warnText = NETCAT_MODERATION_MSG_RETRYPASS . "<br/>";
        $posting = 0;
    }
    // минимальная длина пароля
    $pass_min = $nc_core->get_settings('pass_min', 'auth');
    if ($pass_min && nc_strlen($Password1) < $pass_min) {
        $warnText = sprintf(NETCAT_MODERATION_MSG_PASSMIN, $pass_min) . "<br/>";
        $posting = 0;
    }

    $Password = $Password1;
}


if ($posting) {
    $multiple_changes = +$_POST['multiple_changes'];
    $nc_multiple_changes = isset($nc_multiple_changes) ? (array)$nc_multiple_changes : array();
    reset($nc_multiple_changes);

    $partial = $nc_core->input->fetch_post_get('partial');

    do {
        if ($multiple_changes) {
            $msg_id = key($nc_multiple_changes);
            $multiple_changes_fields = current($nc_multiple_changes);

            if ($msg_id === null && !$multiple_changes_fields) {
                break;
            }

            if (!is_array($multiple_changes_fields)) {
                break;
            }

            next($nc_multiple_changes);

            foreach ($multiple_changes_fields as $multiple_changes_key => $multiple_changes_value) {
                $fldValue[array_search($multiple_changes_key, $fld)] = $multiple_changes_value;
            }
        }

        for ($i = 0; $i < $fldCount; $i++) {
            if ($action === 'change' && !isset(${'f_' . $fld[$i]}) && !isset($_REQUEST['f_' . $fld[$i]]) && !isset($multiple_changes_fields[$fld[$i]])) {
                continue;
            }
            $errCode = 0;

            $nc_field_has_inherited_value = false;
            if ($fldInheritance[$i] && $message && $systemTableID) {
                switch ($nc_core->get_system_table_name_by_id($systemTableID)) {
                    case 'Template':
                        $nc_parent_id = $nc_core->template->get_by_id($message, 'Parent_Template_ID');
                        $nc_parent_field_value = $nc_parent_id ? $nc_core->template->get_by_id($nc_parent_id, $fld[$i]) : null;
                        $nc_field_has_inherited_value = $nc_parent_id && $nc_parent_field_value !== '' && $nc_parent_field_value !== array() && $nc_parent_field_value !== null;
                        break;
                    case 'Subdivision':
                        $nc_parent_id = $nc_core->subdivision->get_by_id($message, 'Parent_Sub_ID');
                        $nc_parent_field_value = $nc_parent_id
                            ? $nc_core->subdivision->get_by_id($nc_parent_id, $fld[$i])
                            : $nc_core->catalogue->get_by_id($nc_core->subdivision->get_by_id($message, 'Catalogue'), $fld[$i]);
                        $nc_field_has_inherited_value = $nc_parent_field_value !== '' && $nc_parent_field_value !== array() && $nc_parent_field_value !== null;
                        break;
                }
            }

            $nc_field_requires_value = $fldNotNull[$i] && !$nc_field_has_inherited_value && ($action === 'add' || !$partial);

            switch ($fldType[$i]) {
                case NC_FIELDTYPE_STRING:
                    $format_string[$i] = nc_field_parse_format($fldFmt[$i], NC_FIELDTYPE_STRING);
                    $fldFmt[$i] = $format_string[$i]['format'];

                    if ($nc_field_requires_value && $fldValue[$i] == '' && !($action === 'change' && $fld[$i] == $AUTHORIZE_BY)) {
                        $errCode = 1;
                    }
                    if ($nc_field_requires_value && $fldFmt[$i] === 'url' && ($fldValue[$i] === 'http://' || $fldValue[$i] === 'ftp://')) {
                        $errCode = 1;
                    }
                    if ($fldFmt[$i] === 'email' && $fldValue[$i] && !nc_preg_match("/^.+@.+\..+$/i", $fldValue[$i])) {
                        $errCode = 2;
                    }
                    if ($fldFmt[$i] === 'phone' && $fldValue[$i] && !nc_preg_match("/^ (\+?\d-?)?  (((\(\d{3}\))|(\d{3})?)-?)?  \d{3}-?\d{2}-?\d{2} $/x", str_replace(array(' ', " \t"), '', $fldValue[$i]))) {
                        $errCode = 2;
                    }
                    if ($fldType[$i] == 1 && $fldFmt[$i] === 'url' && ($fldValue[$i] === 'http://' || $fldValue[$i] === 'ftp://')) {
                        $fldValue[$i] = '';
                    }
                    if ($fldFmt[$i] === "url" && $fldValue[$i] && !isURL($fldValue[$i])) {
                        $errCode = 2;
                    }
                    break;
                case NC_FIELDTYPE_INT:
                    if ($nc_field_requires_value && $fldValue[$i] == '') {
                        $errCode = 1;
                    }
                    if ($fldValue[$i] != '' && $fldValue[$i] != (string)(int)$fldValue[$i]) {
                        $errCode = 2;
                    }
                    break;
                case NC_FIELDTYPE_TEXT:
                    if ($nc_field_requires_value && $fldValue[$i] == '') {
                        $errCode = 1;
                    }
                    break;
                case NC_FIELDTYPE_SELECT:
                    global $db;
                    $ClassificatorName = strtok($fldFmt[$i], ':');
                    if ($nc_field_requires_value && !$fldValue[$i]) {
                        $errCode = 1;
                    }
                    if ($fldValue[$i] != '') {
                        $fldValue[$i] += 0;
                    }

                    $var_name = "f_{$fld[$i]}_name";
                    $var_name_id = "f_{$fld[$i]}_id";
                    if ($fldValue[$i]) {
                        $$var_name = $db->get_var(
                            "SELECT `{$ClassificatorName}_Name`
				             FROM `Classificator_{$ClassificatorName}`
				             WHERE `{$ClassificatorName}_ID`='{$fldValue[$i]}'"
                        );
                    }
                    $$var_name_id = $fldValue[$i];
                    break;
                case NC_FIELDTYPE_BOOLEAN:
                    if ($partial && !isset($_REQUEST["f_{$fld[$i]}"])) {
                        continue;
                    }

                    # если "checkbox"
                    if ($nc_field_requires_value && $fldValue[$i] == "") {
                        $fldValue[$i] = 0;
                    }
                    # если есть значение и оно не "1" и не "NULL", то 1
                    if ($fldValue[$i] && !is_int($fldValue[$i]) && $fldValue[$i] != "NULL") {
                        $fldValue[$i] = 1;
                    }
                    # если значение "NULL" и по умолчанию "1"
                    if ($fldDefault[$i] && $fldValue[$i] == "NULL") {
                        $fldValue[$i] = 1;
                    }
                    break;
                case NC_FIELDTYPE_FILE:
                    $fldValue[$i] = is_array(${'f_' . $fld[$i]})
                        ? $_FILES['f_' . $fld[$i]]['tmp_name']
                        : stripslashes(${'f_' . $fld[$i]});
                    $fldValue[$i] = trim($fldValue[$i]);
                    $is_file_uploaded = $fldValue[$i] && $fldValue[$i] !== 'none';

                    $oldValue = null;
                    if ($action === 'change') {
                        $oldValue = ${"f_{$fld[$i]}_old"};
                        $old_file_values[$i] = $oldValue;
                        if (!empty(${'f_KILL' . $fldID[$i]}) && !$is_file_uploaded) {
                            $fldValue[$i] = '';
                            $file_to_delete[] = $i;
                        } else if ($oldValue && !$is_file_uploaded) {
                            $fldValue[$i] = $oldValue;
                        }
                    }

                    if ($nc_field_requires_value && ($fldValue[$i] == '' || $fldValue[$i] === 'none')) {
                        $errCode = 6;
                    }
                    if ($is_file_uploaded && !$oldValue && (!file_exists($fldValue[$i]) || !@filesize($fldValue[$i]))) {
                        $errCode = 2;
                    }
                    if (!$fldValue[$i] && ($_FILES['f_' . $fld[$i]]['error'] == 1 || $_FILES['f_' . $fld[$i]]['error'] == 2)) {
                        $errCode = 7;
                    }

                    if ($is_file_uploaded && is_uploaded_file($fldValue[$i])) {
                        $parsedFormat = nc_field_parse_format($fldFmt[$i], NC_FIELDTYPE_FILE);
                        $fldFS[$i] = $parsedFormat['fs'];
                        // закачиваемый файл?
                        $fldDisposition[$i] = $parsedFormat['disposition'] ? 1 : 0;
                        //$fileSettings = explode(":",$fldFmt[$i]);
                        $filetype = $_FILES['f_' . $fld[$i]]['type'];
                        $filesize = $_FILES['f_' . $fld[$i]]['size'];
                        if ($parsedFormat['size'] > 0 && ($filesize > $parsedFormat['size'])) {
                            $errCode = 7;
                        }
                        if (!empty($parsedFormat['type'])) {
                            $filetypeNotAllowed = true;
                            $filetypeParsed = explode('/', $filetype);

                            foreach ($parsedFormat['type'] as $v) {
                                if ($filetypeParsed[0] != $v[0]) {
                                    continue;
                                }
                                if ($filetypeParsed[1] == $v[1] || $v[1] === '*') {
                                    $filetypeNotAllowed = false;
                                    break;
                                }
                            }

                            if ($filetypeNotAllowed) {
                                $errCode = 8;
                            }
                        }
                    }

                    if ($errCode) {
                        $fldValue[$i] = $oldValue;
                    }
                    break;
                case NC_FIELDTYPE_FLOAT:
                    if ($nc_field_requires_value && $fldValue[$i] == '') {
                        $errCode = 1;
                    }
                    if ($fldValue[$i] != '' && !preg_match("/^\-?[0-9]+(\.[0-9]+)?$/is", str_replace(',', '.', $fldValue[$i]))) {
                        $errCode = 2;
                    }
                    if (preg_match('/,/', $fldValue[$i])) {
                        $fldValue[$i] = str_replace(',', '.', $fldValue[$i]);
                    }

                    break;
                case NC_FIELDTYPE_DATETIME:
                    if ($nc_field_requires_value && $fldValue[$i] == '') {
                        $errCode = 1;
                    }
                    if ($fldValue[$i] != '' && $fldValue[$i] !== '0000-00-00 00:00:00' && $fldFmt[$i] !== 'event_time' && !checkdate(nc_substr($fldValue[$i], 5, 2), nc_substr($fldValue[$i], 8, 2), nc_substr($fldValue[$i], 0, 4))) {
                        $errCode = 2;
                        $fldValue[$i] = '';
                    }
                    break;
                case NC_FIELDTYPE_RELATION:
                    if ($fldValue[$i]) {
                        $fldValue[$i] = (int)$fldValue[$i];
                    }
                    if ($nc_field_requires_value && !$fldValue[$i]) {
                        $errCode = 1;
                    }
                    break;
                case NC_FIELDTYPE_MULTISELECT:
                    if ($partial && !isset($_REQUEST["f_{$fld[$i]}"])) {
                        continue;
                    }

                    if ($nc_field_requires_value && !count($fldValue[$i])) {
                        $errCode = 1;
                    }
                    $ClassificatorName = strtok($fldFmt[$i], ':');
                    $tmp = ',';
                    $var_name = "f_{$fld[$i]}_name";
                    $var_name_id = "f_{$fld[$i]}_id";

                    if (!empty($fldValue[$i])) {
                        foreach ($fldValue[$i] as $v) {
                            if ($v == '') {
                                continue;
                            }
                            $tmp .= $v . ',';
                            ${$var_name_id}[] = $v;
                        }
                        $fldValue[$i] = $tmp;

                        if (!empty($$var_name_id)) {
                            $$var_name = $db->get_col(
                                "SELECT `{$ClassificatorName}_Name`
  				                 FROM `Classificator_" . strtok($fldFmt[$i], ':') . "`
  				                 WHERE `{$ClassificatorName}_ID` IN (" . implode(',', $$var_name_id) . ')'
                            );
                        }
                    } else {
                        $fldValue[$i] = '';
                        $$var_name = array();
                    }

                    unset($ClassificatorName);
                    break;
                case NC_FIELDTYPE_MULTIFILE:
                    $nc_multifield_saver = nc_multifield_saver::with_post_data(
                        $user_table_mode ? 'User' : $classID,
                        ($action === 'add') ? null : ($user_table_mode ? $UserID : $message),
                        ${"f_{$fld[$i]}"}
                    );

                    if (!$nc_multifield_saver->has_post_data()) {
                        break;
                    }

                    if ($nc_multifield_saver->has_file_upload_size_error()) {
                        $errCode = 7;
                        break;
                    }

                    if (!$nc_multifield_saver->check_settings_hash()) {
                        die('Settings integrity error');
                    }

                    $multifile_warnText = $nc_multifield_saver->get_error_string();

                    unset($nc_multifield_saver);
                    break;
            }

            if ($user_table_mode) {
                // проверка  поля, по которму идет авторизация
                if ($fld[$i] === $AUTHORIZE_BY && ($e = $nc_core->user->check_login($fldValue[$i], $action === 'add' ? 0 : $message))) {
                    if ($e == NC_AUTH_LOGIN_EXISTS) {
                        $warnText = sprintf(NETCAT_MODERATION_MSG_LOGINALREADY . '<br>', $fldValue[$i]);
                    }
                    if ($e == NC_AUTH_LOGIN_INCORRECT) {
                        $warnText = NETCAT_MODERATION_MSG_LOGININCORRECT . '<br>';
                    }
                    $posting = 0;
                    break;
                }
            }

            $warnUser = false;

            if ($fldTypeOfEdit[$i] == NC_FIELD_PERMISSION_EVERYONE) {
                $warnUser = true;
            } elseif ($fldTypeOfEdit[$i] == NC_FIELD_PERMISSION_ADMIN) {
                $warnUser = $admin_mode;
            }

            if ($warnUser && $errCode) {
                $warnText = $errDescr[$errCode];
                $warnText = str_replace('%NAME', $fldName[$i] ?: $fld[$i], $warnText);
                $posting = 0;
                break;
            }

            if ($multifile_warnText) {
                $warnText = $multifile_warnText;
                $posting = 0;
                break;
            }
        }

    } while ($multiple_changes);

    # проверка изображения на картинке
    if (!$AUTH_USER_ID && $action === "add" && $current_cc['UseCaptcha'] && $MODULE_VARS['captcha']) {
        if (!nc_captcha_verify_code($nc_captcha_code)) {
            $warnText = NETCAT_MODULE_CAPTCHA_WRONG_CODE;
            $posting = 0;
        }
    }

    // в случае ошибки нужно сохранить предыдущие значения полей типа файл
    if (!$posting && !empty($old_file_values)) {
        foreach ($old_file_values as $k => $v) {
            $fldValue[$k] = $v;
        }
    }

    if ($posting && !empty($file_to_delete)) {
        // Редактируем пользователя
        if ($user_table_mode) {
            // Пользователь должен быть авторизованным и
            // Иметь доступ к изменению пользователя (если это не он сам)
            if (!$perm instanceof Permission || ($AUTH_USER_ID != $message && !$perm->isAccess(NC_PERM_ITEM_USER, NC_PERM_ACTION_EDIT, $message, $posting))) {
                $warnText = NETCAT_MODERATION_ERROR_NORIGHTS;
                $posting = 0;
            }
        }

        // Редактируем объект
        if (!$user_table_mode) {
            // Должно быть верным хотя бы одно из предположений ниже
            if (!(
                // Редактирование доступно для всех
                $cc_env['Edit_Access_ID'] == 1 ||
                // Редактирование доступно для зарегистрированных пользователей и текущий пользователь авторизован
                ($cc_env['Edit_Access_ID'] == 2 && $AUTH_USER_ID) ||
                // Редактирование доступно уполномоченным и у текущего пользователя есть права
                ($perm instanceof Permission && $perm->isSubClass($cc, MASK_EDIT))
            )) {
                $warnText = NETCAT_MODERATION_ERROR_NORIGHTS;
                $posting = 0;
            }
        }

        if ($posting) {
            // ошибок при заполнении формы нет - можно удалить файлы
            foreach ($file_to_delete as $v) {
                DeleteFile($fldID[$v], $fld[$v], $classID, $systemTableName, $message);
            }
        }
    }

    unset($old_file_values);
    unset($file_to_delete);
}

// обертка для вывода ошибки в админке
if ($warnText && ($nc_core->inside_admin || $isNaked)) {
    ob_start();
    nc_print_status($warnText, 'error');
    $warnText = ob_get_clean();
}
