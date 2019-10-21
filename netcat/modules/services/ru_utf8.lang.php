<?php

define("NETCAT_MODULE_SERVICES", "Внешние сервисы");
define("NETCAT_MODULE_SERVICES_DESCRIPTION", "Данный модуль предназначен для управления внешними и внутренними рекламными кампаниями и SEO сайта.");

define("NETCAT_MODULE_SERVICES_ERROR", "Ошибка");
define("NETCAT_MODULE_SERVICES_ERROR_CODE", "код");
define("NETCAT_MODULE_SERVICES_ERROR_STRING", "текст");
define("NETCAT_MODULE_SERVICES_ERROR_DETAILS", "детали");
define("NETCAT_MODULE_SERVICES_MISSING", "отсутствуют");
  
define("NETCAT_MODULE_SERVICES_BUTTON_SAVE", "Сохранить");
define("NETCAT_MODULE_SERVICES_BUTTON_ADD", "Добавить");
define("NETCAT_MODULE_SERVICES_BUTTON_BACK", "Назад");

define("NETCAT_MODULE_SERVICES_TITLE", "Внешние сервисы");
define("NETCAT_MODULE_SERVICES_EMPTY_TOKEN", "<a href='http://api.yandex.ru/direct/doc/concepts/auth-token.xml' target='_blank'>Авторизационный токен Яндекса</a> не задан в Настройках модуля. Чтобы получить токен, воспользуйтесь <a href='http://api.yandex.ru/oauth/doc/dg/tasks/get-oauth-token.xml' target='_blank'>Инструкцией по получению токена</a>.");

define("NETCAT_MODULE_SERVICES_SETTINGS", "Настройки модуля");
define("NETCAT_MODULE_SERVICES_YANDEX_AUTH_TOKEN", "Авторизационный токен Яндекса");

define("NETCAT_MODULE_SERVICES_CONFIRM_DELETE", "Вы уверены, что хотите удалить '%s'?");

define("NETCAT_MODULE_SERVICES_METRIKA", "Яндекс.Метрика");

define("NETCAT_MODULE_SERVICES_ACTION_ADDFUNDS", "Пополнить");
define("NETCAT_MODULE_SERVICES_ACTION_ARCHIVE", "Архивировать");
define("NETCAT_MODULE_SERVICES_ACTION_UNARCHIVE", "Распаковать");
define("NETCAT_MODULE_SERVICES_ACTION_STOP", "Остановить");
define("NETCAT_MODULE_SERVICES_ACTION_RESUME", "Возобновить");
define("NETCAT_MODULE_SERVICES_ACTION_MODERATE", "Отправить на модерацию");

define("NETCAT_MODULE_SERVICES_METRIKA_NO_COUNTERS", "Счетчики не найдены");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_ERR_CONNECT", "Не удалось проверить (ошибка соединения).");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_ERR_DUPLICATED", "Установлен более одного раза.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_ERR_HTML_CODE", "Установлен некорректно.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_ERR_OTHER_HTML_CODE", "Уже установлен другой счетчик.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_ERR_TIMEOUT", "Не удалось проверить (превышено время ожидания).");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_ERR_UNKNOWN", "Неизвестная ошибка.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_NEW_COUNTER", "Недавно создан.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_NA", "Не применим к данному счетчику.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_NOT_EVERYWHERE", "Установлен не на всех страницах.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_NOT_FOUND", "Не установлен.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_NOT_FOUND_HOME", "Не установлен на главной странице.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_NOT_FOUND_HOME_LOAD_DATA", "Не установлен на главной странице, но данные поступают.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_OBSOLETE", "Установлена устаревшая версия кода счетчика.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_OK", "Корректно установлен.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_OK_NO_DATA", "Установлен, но данные не поступают.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_WAIT_FOR_CHECKING", "Ожидает проверки наличия.");
define("NETCAT_MODULE_SERVICES_METRIKA_CS_WAIT_FOR_CHECKING_LOAD_DATA", "Ожидает проверки наличия, данные поступают.");

define("NETCAT_MODULE_SERVICES_METRIKA_CP_OWN", "Полный доступ");
define("NETCAT_MODULE_SERVICES_METRIKA_CP_VIEW", "Гостевой: просмотр");
define("NETCAT_MODULE_SERVICES_METRIKA_CP_EDIT", "Гостевой: полный доступ");

define("NETCAT_MODULE_SERVICES_METRIKA_COUNTERS", "Список счётчиков");

define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_NAME", "Название");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_SITE", "Сайт");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_ID", "№ счётчика");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_STATUS", "Статус");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_PERMISSION", "Права доступа");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_VIEW_STAT", "Статистика");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_EDIT", "Редактировать счётчик");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_ADD", "Добавить счётчик");

define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_HREF_PREFIX", "http://");
define("NETCAT_MODULE_SERVICES_METRIKA_COUNTER_CODE", "Код для вставки");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TRAFFIC", "Посещаемость");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_SOURCES", "Источники");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_USERS", "Посетители");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_CONTENT", "Содержание");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH", "Технологии");

define("NETCAT_MODULE_SERVICES_METRIKA_STAT_DATE", "Дата");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_VISITS", "Визиты");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_VIEWS", "Просмотры");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_VISITORS", "Посетители");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TOTAL", "Итого");

define("NETCAT_MODULE_SERVICES_TODAY", "сегодня");
define("NETCAT_MODULE_SERVICES_YESTERDAY", "вчера");
define("NETCAT_MODULE_SERVICES_WEEK", "неделя");
define("NETCAT_MODULE_SERVICES_MONTH", "месяц");

define("NETCAT_MODULE_SERVICES_WEEK_ABBR_0", "ПН");
define("NETCAT_MODULE_SERVICES_WEEK_ABBR_1", "ВТ");
define("NETCAT_MODULE_SERVICES_WEEK_ABBR_2", "СР");
define("NETCAT_MODULE_SERVICES_WEEK_ABBR_3", "ЧТ");
define("NETCAT_MODULE_SERVICES_WEEK_ABBR_4", "ПТ");
define("NETCAT_MODULE_SERVICES_WEEK_ABBR_5", "СБ");
define("NETCAT_MODULE_SERVICES_WEEK_ABBR_6", "ВС");

define("NETCAT_MODULE_SERVICES_METRIKA_GROUP_BY", "Группировать");
define("NETCAT_MODULE_SERVICES_BY_DAY", "дням");
define("NETCAT_MODULE_SERVICES_BY_WEEK", "неделям");
define("NETCAT_MODULE_SERVICES_BY_MONTH", "месяцам");
define("NETCAT_MODULE_SERVICES_OVER_PERIOD", "За период");
define("NETCAT_MODULE_SERVICES_DATA_NOT_AVAILABLE", "Данные недоступны");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_SOURCE", "Источник");

define("NETCAT_MODULE_SERVICES_FILTER_FROM", "от");
define("NETCAT_MODULE_SERVICES_FILTER_TO", "до");
define("NETCAT_MODULE_SERVICES_FILTER_SHOW", "Показать");

define("NETCAT_MODULE_SERVICES_SOURCES_SUMMARY", "Сводка");
define("NETCAT_MODULE_SERVICES_SOURCES_SITES", "Сайты");
define("NETCAT_MODULE_SERVICES_SOURCES_SEARCH_ENGINES", "Поисковые системы");
define("NETCAT_MODULE_SERVICES_SOURCES_PHRASES", "Поисковые фразы");

define("NETCAT_MODULE_SERVICES_METRIKA_STAT_GEO", "Местоположение");

define("NETCAT_MODULE_SERVICES_CONTENT_POPULAR", "Популярное");
define("NETCAT_MODULE_SERVICES_CONTENT_ENTRANCE", "Страницы входа");
define("NETCAT_MODULE_SERVICES_CONTENT_EXIT", "Страницы выхода");
define("NETCAT_MODULE_SERVICES_CONTENT_TITLES", "по заголовкам");
define("NETCAT_MODULE_SERVICES_CONTENT_URL_PARAM", "по параметрам URL");

define("NETCAT_MODULE_SERVICES_CONTENT_HEAD_URL", "URL");
define("NETCAT_MODULE_SERVICES_CONTENT_HEAD_TITLE", "Заголовок");
define("NETCAT_MODULE_SERVICES_CONTENT_HEAD_URL_PARAM", "Параметры URL");

define("NETCAT_MODULE_SERVICES_METRIKA_STAT_ENTRANCES", "Входы");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_EXITS", "Выходы");

define("NETCAT_MODULE_SERVICES_TECH_BROWSERS", "Браузеры");
define("NETCAT_MODULE_SERVICES_TECH_OS", "Операционные системы");
define("NETCAT_MODULE_SERVICES_TECH_DISPLAY", "Разрешения дисплеев");
define("NETCAT_MODULE_SERVICES_TECH_MOBILE", "Мобильные устройства");

define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH_BROWSERS", "Браузер");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH_OS", "Операционная система");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH_DISPLAY", "Разрешение");
define("NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH_MOBILE", "Мобильный телефон/КПК");

?>