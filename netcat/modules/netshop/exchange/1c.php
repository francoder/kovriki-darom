<?php

// #####################################################################################################################
// #                                                    НАСТРОЙКА                                                      #
// #####################################################################################################################

// Подключим ядро
$netcat_folder = dirname(dirname(dirname(__DIR__)));
require $netcat_folder . '/connect_io.php';
$nc_core = nc_core::get_object();
$nc_core->modules->load_env('ru');

// Получим текущий URL и починим его
$current_url = $nc_core->url->get_full_url();
$current_url = urldecode($current_url);
$current_url_parts = explode('?', $current_url);
$first_url_part = array_shift($current_url_parts);
$current_url = $first_url_part . '?' . implode('&', $current_url_parts);

// Получим параметры type, mode, filename
$current_url_query = parse_url($current_url, PHP_URL_QUERY);
parse_str($current_url_query, $current_url_params);
$exchange_object_id = !empty($current_url_params['id']) ? $current_url_params['id'] : null;
$type = !empty($current_url_params['type']) ? $current_url_params['type'] : null;
$mode = !empty($current_url_params['mode']) ? $current_url_params['mode'] : null;
$file_name = !empty($current_url_params['filename']) ? $current_url_params['filename'] : null;

// Максимальный размер передаваемого файла в байтах
$max_file_size = 1024 * 50;
$ini_upload_max_filesize = nc_netshop_exchange_helper::parse_size(ini_get('upload_max_filesize'));
$ini_post_max_size = nc_netshop_exchange_helper::parse_size(ini_get('post_max_size'));
if ($max_file_size > $ini_upload_max_filesize) {
    $max_file_size = $ini_upload_max_filesize;
}
if ($max_file_size > $ini_post_max_size) {
    $max_file_size = $ini_post_max_size;
}
$cookie_name = "nc-exchange-import-cookie";
// Согласно документации перенос строк должен быть таков
$eol = "\n";

function exit_with_error($error) {
    $message = "ERROR: {$error};";
    log_to_file($message);
    echo $message;
    exit;
}

function log_to_file($message) {
//    $file_path = __DIR__ . '/logs.txt';
//    $date = date('d.m.Y H:i:s');
//    $message = "[{$date}] {$message}\n";
//    file_put_contents($file_path, $message, FILE_APPEND);
}

// #####################################################################################################################
// #                                                   ПРЕДПРОВЕРКА                                                    #
// #####################################################################################################################

// Проверим присланный id и и объект импорта
if (empty($exchange_object_id)) {
    exit_with_error('EXCHANGE OBJECT ID NOT FOUND');
}
try {
    $exchange_object = nc_netshop_exchange_import::by_id($exchange_object_id);
} catch (Exception $e) {
    exit_with_error('EXCHANGE OBJECT NOT FOUND');
}
// Проверим тип и формат найденного объекта импорта
$is_right_format = $exchange_object->get('format') == nc_netshop_exchange_import::FORMAT_CML;
$is_right_mode = $exchange_object->get_mode() == nc_netshop_exchange_object::MODE_AUTOMATED;
if (!$is_right_format || !$is_right_mode) {
    exit_with_error("THIS EXCHANGE OBJECT CAN'T BE USED FOR AUTOMATED EXCHANGE");
}
// Проверим авторизацию
$secret_name = $secret_key = null;
$catalogue_id = $exchange_object->get_catalogue_id();
$nc_netshop = nc_netshop::get_instance($catalogue_id);
if ($nc_netshop->is_netshop_v1_in_use()) {
    $MODULE_VARS = $nc_core->modules->get_module_vars();
    $secret_name = $MODULE_VARS['netshop']['SECRET_NAME'];
    $secret_key = $MODULE_VARS['netshop']['SECRET_KEY'];
} else {
    $secret_name = $nc_netshop->get_setting('1cSecretName');
    $secret_key = $nc_netshop->get_setting('1cSecretKey');
}
if (
    !isset($_SERVER['PHP_AUTH_USER']) ||
    !($_SERVER['PHP_AUTH_USER'] == $secret_name && $_SERVER['PHP_AUTH_PW'] == $secret_key)
) {
    // Авторизация неуспешна
    header('WWW-Authenticate: Basic realm="Authorization required"');
    header('HTTP/1.0 401 Unauthorized');
    exit_with_error('WRONG LOGIN OR PASSWORD');
}

// Если объект обмена не настроен, то мы в режиме перехвата файлов (без последующего импорта)
$only_intercept_files = !$exchange_object->is_automated_mode_enabled();

// #####################################################################################################################
// #                                                    ОБРАБОТКА                                                      #
// #####################################################################################################################

// ВЫГРУЖАЕМЫЙ ТИПА ДАННЫХ
switch ($type) {
    // КАТАЛОГ ТОВАРОВ
    case 'catalog': {
        // ЭТАП ВЫГРУЗКИ
        switch ($mode) {
            // ПРОВЕРКА АВТОРИЗАЦИИ
            case 'checkauth': {
                log_to_file('Выгрузка каталога товаров : Проверка авторизации');

                // Проверка авторизации происходит перед обработкой, поэтому выведем success
                echo 'success' . $eol;
                echo $cookie_name . $eol;
                echo uniqid();
                exit;
            }
            // ИНИЦИАЛИЗАЦИЯ ВЫГРУЗКИ
            case 'init': {
                log_to_file('Выгрузка каталога товаров : Инициализация выгрузки');

                // Отдача ответа 1С
                echo 'zip=no' . $eol;
                echo 'file_limit=' . $max_file_size;
                exit;
            }
            // СОХРАНЕНИЕ ФАЙЛОВ
            case 'file': {
                // Импортер файлов импорта
                $importer = new nc_netshop_exchange_import_cml_importer($exchange_object);
                // Содержимое файла или часть содержимого
                $file_content = file_get_contents("php://input");
                $file_data = $importer->import_catalog_file($file_name, $file_content);
                log_to_file("Выгрузка каталога товаров : Получение файлов : Файл {$file_name} (записано {$file_data['size']} байт по адресу {$file_data['path']})");

                // Отдача ответа 1С
                echo "success";
                exit;
            }
            // ИМПОРТ ЗАГРУЖЕННЫХ ФАЙЛОВ
            case 'import': {
                if (!$only_intercept_files) {
                    log_to_file("Выгрузка каталога товаров : Попытка запуска импорта");
                    if ($exchange_object->has_acceptable_files()) {
                        log_to_file("Выгрузка каталога товаров : Импорт был запущен");
                        $exchange_object->run();
                    } else {
                        log_to_file("Выгрузка каталога товаров : Импорт не был запущен, т.к. не хватает важных файлов");
                    }
                } else {
                    log_to_file("Выгрузка каталога товаров : Импорт не запущен, режим перехвата файлов");
                }

                // Отдача ответа 1С
                echo 'success';
                exit;
            }
        }

        break;
    }

    // ЗАКАЗЫ
    case 'sale': {
        // Заглушка
        break;
    }
}