<?php

/**
 * Поиск населённых пунктов по названию.
 *
 * Возвращает массив в JSON с элементами:
 *   country_name — страна
 *   region_name — область
 *   district_name — район
 *   locality_name — населённый пункт
 *   name — «уникальное» в пределах страны название населённого пункта
 *      (возможно, с добавлением района/области). Для заполнения поля «Город» заказа
 *      должно использоваться это значение
 *   is_exact_match (только если true) — результат является точным совпадением с запросом
 *   latitude, longitude — координаты населённого пункта (только если известны)
 */

require realpath(__DIR__ . '/../../../../') . '/vars.inc.php';
require_once $INCLUDE_FOLDER . 'index.php';

$nc_core = nc_core::get_object();
$netshop = nc_netshop::get_instance();

$query = $nc_core->input->fetch_post_get('location');
$result = $netshop->location->find_locations($query)->to_compact_array();

header('Content-Type: application/json');
echo nc_array_json($result);
