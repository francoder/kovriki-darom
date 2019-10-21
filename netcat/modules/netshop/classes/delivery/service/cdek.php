<?php

/**
 * Расчёт доставки СДЭК.
 * Поддерживается только доставка по России, только в населённые пункты с почтовым индексом.
 */
class nc_netshop_delivery_service_cdek extends nc_netshop_delivery_service {

    const CDEK_CALCULATE_URL = 'http://api.cdek.ru/calculator/calculate_price_by_json.php';
    const CDEK_PICKUP_POINTS_URL = 'http://integration.cdek.ru/pvzlist.php';

    /** @var string название службы */
    protected $name = NETCAT_MODULE_NETSHOP_DELIVERY_CDEK;

    /** @var string тип доставки */
    protected $delivery_type = nc_netshop_delivery::DELIVERY_TYPE_MULTIPLE;

    /** @var bool служба может предложить более одного варианта доставки */
    protected $can_provide_multiple_variants = true;

    /**
     * Поля, которым нужны соответствия
     * @var array
     */
    protected $mapped_fields = array(
        'from_city' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_FROM_CITY,
        'to_city' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_TO_CITY,
        'to_zipcode' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_TO_ZIP_CODE,
    );

    /** @var array|string тарифы СДЭК, см. https://www.cdek.ru/clients/integrator.html */
    protected $rate_types_data = array(
        'shipping_from_cdek' => array(
            // логин нужен
            136 => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 30, 'requires_login' => true,  'name' => 'посылка до пункта выдачи'),
            137 => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 30, 'requires_login' => true,  'name' => 'посылка на указанный адрес'),
            234 => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 50, 'requires_login' => true,  'name' => 'экономичная посылка до пункта выдачи'),
            233 => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 50, 'requires_login' => true,  'name' => 'экономичная посылка на указанный адрес'),

            // логин не нужен

            // по следующему тарифу никогда нет результатов?
            // 5   => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 0,  'requires_login' => false, 'name' => 'экономичная доставка до пункта выдачи'),

            10  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 0,  'requires_login' => false, 'name' => 'экспресс-доставка до пункта выдачи', 'ids' => array(10, 15)),
            11  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 0,  'requires_login' => false, 'name' => 'экспресс-доставка на указанный адрес', 'ids' => array(11, 16)),

            62  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 0,  'requires_login' => false, 'name' => 'магистральный экспресс'),
            63  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 0,  'requires_login' => false, 'name' => 'магистральный супер-экспресс'),
        ),

        'shipping_from_shop' => array(
            // логин нужен
            138 => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 30, 'requires_login' => true,  'name' => 'посылка до пункта выдачи'),
            139 => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 30, 'requires_login' => true,  'name' => 'посылка на указанный адрес'),

            // логин не нужен
            12  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_PICKUP,  'max_weight' => 0,  'requires_login' => false, 'name' => 'экспресс-доставка до пункта выдачи', 'ids' => array(12, 17)),
            1   => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 0,  'requires_login' => false, 'name' => 'экспресс-доставка на указанный адрес', 'ids' => array(1, 18)),

            57  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 5,  'requires_login' => false, 'name' => 'супер-экспресс до 9'),
            58  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 5,  'requires_login' => false, 'name' => 'супер-экспресс до 10'),
            59  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 5,  'requires_login' => false, 'name' => 'супер-экспресс до 12'),
            60  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 5,  'requires_login' => false, 'name' => 'супер-экспресс до 14'),
            61  => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 30, 'requires_login' => false, 'name' => 'супер-экспресс до 16'),
            3   => array('type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER, 'max_weight' => 30, 'requires_login' => false, 'name' => 'супер-экспресс до 18'),
        ),
    );

    /** @var array тарифы по умолчанию */
    protected $default_rate_types = array(
        10, 11, 62, // отправка со склада СДЭК
        1, 12,      // забор курьером из магазина
    );

    /** @var array выбранные в настройках тарифы */
    protected $enabled_rate_types = array();

    /** @var array  */
    protected $delivery_point_cache = array();

    /**
     * @param array $data
     */
    public function __construct(array $data = array()) {
        parent::__construct($data);
        $nc_core = nc_core::get_object();
        if (!$nc_core->NC_UNICODE) {
            $this->rate_types_data = $nc_core->utf8->array_utf2win($this->rate_types_data);
        }
    }


    /**
     * Возвращает массив с описанием дополнительных настроек способа доставки
     * (в формате, подходящем для nc_a2f).
     *
     * @return array
     */
    public function get_settings_fields() {
        return array(
            'login' => array(
                'type' => 'string',
                'caption' => NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_LOGIN,
            ),
            'password' => array(
                'type' => 'string',
                'caption' => NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_PASSWORD,
            ),
            'shipment_type' => array(
                'type' => 'select',
                'subtype' => 'static',
                'caption' => NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_SHIPMENT_TYPE,
                'values' => array(
                    'shipping_from_cdek' => NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_SHIPMENT_TYPE_CDEK,
                    'shipping_from_shop' => NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_SHIPMENT_TYPE_SHOP,
                ),
                'default_value' => 'shipping_from_cdek',
            ),
            'rate_types_selection' => array(
                'type' => 'custom',
                'html' => new nc_ui_view(__DIR__ . '/cdek/rate_types', array(
                    'all_rate_types' => $this->rate_types_data,
                    'default_rate_types' => $this->default_rate_types,
                )),
            ),
            'rate_types' => array(
                'type' => 'hidden',
                'value' => json_encode($this->default_rate_types), // значение по умолчанию
            ),
        );
    }

    /**
     * @param array $settings
     */
    public function set_settings(array $settings) {
        parent::set_settings($settings);
        $this->enabled_rate_types = json_decode($this->get_setting('rate_types' ?: '[]'), true) ?: $this->default_rate_types;
    }

    /**
     * @return null
     */
    public function calculate_delivery() {
        return null;
    }

    /**
     * @param nc_netshop_delivery_method $method
     * @return nc_netshop_delivery_method_collection
     */
    public function get_variants(nc_netshop_delivery_method $method) {
        $variants = new nc_netshop_delivery_method_collection();

        $common_request_data = $this->get_common_calculation_request_data($method);
        if (!$common_request_data) {
            return $variants;
        }

        $has_login = strlen($this->get_setting('login')) > 0;
        $total_weight = $this->data['weight'] / 1000;

        foreach ($this->enabled_rate_types as $rate_id) {
            $rate_data = nc_array_value($this->rate_types_data[$this->get_setting('shipment_type')], $rate_id);
            $skip =
                !$rate_data ||
                (!empty($rate_data['requires_login']) && !$has_login) ||
                (!empty($rate_data['max_weight']) && $total_weight > $rate_data['max_weight']);
            if (!$skip) {
                $this->add_cdek_delivery_variant($variants, $common_request_data, $rate_id, $rate_data, $total_weight);
            }
        }

        return $variants;
    }

    /**
     * Возвращает параметры для запроса расчёта доставки, кроме идентификатора тарифа
     * @param nc_netshop_delivery_method $method
     * @return array|null
     */
    protected function get_common_calculation_request_data(nc_netshop_delivery_method $method) {
        $from_code = $this->get_postal_code('from');
        if (!$from_code) {
            return null;
        }

        $to_code = $this->get_postal_code('to');
        if (!$to_code) {
            return null;
        }

        $shipment_date = date("Y-m-d", strtotime('+' . (int)$method->get_days_until_shipment() . ' days'));
        $request_data = array(
            'version' => '1.0',
            'dateExecute' => $shipment_date,
            'senderCityPostCode' => (string)$from_code,
            'receiverCityPostCode' => (string)$to_code,
            'goods' => array(),
        );

        // Логин и подпись
        $login = trim($this->get_setting('login'));
        if ($login) {
            $password = trim($this->get_setting('password'));
            $request_data['authLogin'] = $login;
            $request_data['secure'] = md5($shipment_date . '&' . $password);
        }

        // Товары
        /** @var nc_netshop_item_collection $items */
        $items = nc_array_value($this->data, 'items');
        if (!$items) {
            return null;
        }

        $netshop = nc_netshop::get_instance($method->get('catalogue_id'));
        $default_size_1 = $netshop->get_setting('DefaultPackageSize1');
        $default_size_2 = $netshop->get_setting('DefaultPackageSize2');
        $default_size_3 = $netshop->get_setting('DefaultPackageSize3');
        $default_weight = 100;

        foreach ($items as $item) {
            $request_data['goods'][] = array(
                'weight' => sprintf('%0.3F', ($item['Weight'] ?: $default_weight) / 1000),
                'length' => sprintf('%d', $item['PackageSize1'] ?: $default_size_1),
                'width' =>  sprintf('%d', $item['PackageSize2'] ?: $default_size_2),
                'height' => sprintf('%d', $item['PackageSize3'] ?: $default_size_3),
            );
        }

        return $request_data;
    }

    /**
     * @param $variants
     * @param $request_data
     * @param $rate_id
     * @param $rate_data
     * @param $total_weight_in_kg
     */
    protected function add_cdek_delivery_variant(nc_netshop_delivery_method_collection $variants, $request_data, $rate_id, $rate_data, $total_weight_in_kg) {
        if (isset($rate_data['ids'])) {
            $request_data['tariffList'] = array();
            foreach ($rate_data['ids'] as $i => $id) {
                $request_data['tariffList'][] = array('priority' => $i, 'id' => $id);
            }
        } else {
            $request_data['tariffId'] = $rate_id;
        }

        $response = $this->make_http_request(
            self::CDEK_CALCULATE_URL,
            json_encode($request_data),
            array('Content-Type: application/json')
        );

        $result = json_decode($response, true);

        if (!$result) {
            return;
        }

        if (!empty($result['error'])) {
            foreach ($result['error'] as $error) {
                // 3 — Невозможно осуществить доставку по этому направлению при заданных условиях
                // 15 — Почтовый индекс города-получателя отсутствует в базе СДЭК
                if ($error['code'] != 3 && $error['code'] != 15) {
                    $this->show_message_for_supervisor($error['text']);
                }
            }
            return;
        }

        if (empty($result['result'])) {
            return;
        }

        $delivery_variant = new nc_netshop_delivery_method_variant(array(
            'id' => $rate_id,
            'name' => "$this->name ($rate_data[name])",
            'delivery_type' => $rate_data['type'],
            'description' => '',
            'extra_charge_absolute' => $result['result']['price'],
            'minimum_delivery_days' => $result['result']['deliveryPeriodMin'],
            'maximum_delivery_days' => $result['result']['deliveryPeriodMax'],
        ));

        if ($rate_data['type'] === nc_netshop_delivery::DELIVERY_TYPE_PICKUP) {
            $delivery_points = $this->get_cdek_delivery_points_by_postal_code($request_data['receiverCityPostCode'], $total_weight_in_kg);
            if ($delivery_points) {
                // СДЭК может предложить доставку в одни и те же ПВЗ с разными
                // условиями (сроками и ценами). Если идентификатор у delivery_points
                // будет одинаковый у разных вариантов доставки, при выборе ПВЗ
                // на карте в списке будет выбран ПВЗ из последнего варианта.
                // Чтобы такого не происходило, модифицируем ID...
                if ($variants->any('delivery_type', nc_netshop_delivery::DELIVERY_TYPE_PICKUP)) {
                    $delivery_points = clone $delivery_points;
                    foreach ($delivery_points as $delivery_point) {
                        $delivery_point->set_id($delivery_point->get_id() . '#' . $rate_id);
                    }
                }
                $delivery_variant->set_delivery_points($delivery_points);
            }
        }

        $variants->add($delivery_variant);
    }


    /**
     * @param string $direction 'to' | 'from'
     * @return null|string
     */
    protected function get_postal_code($direction) {
        if (!empty($this->data[$direction . '_zipcode'])) {
            return $this->data[$direction . '_zipcode'];
        }
        $location_data = nc_array_value($this->data, $direction . '_location_data');
        if (!$location_data instanceof nc_netshop_location_data) {
            return null;
        }
        return nc_netshop_location_provider_russianpost::get_first_postal_code($location_data);
    }

    /**
     * @param $postal_code
     * @param $total_weight_in_kg
     * @return nc_netshop_delivery_point_collection|null
     */
    public function get_cdek_delivery_points_by_postal_code($postal_code, $total_weight_in_kg) {
        if (!$postal_code) {
            return null;
        }

        $cache_key = "$postal_code:$total_weight_in_kg";
        if (isset($this->delivery_point_cache[$cache_key])) {
            return $this->delivery_point_cache[$cache_key];
        }

        if (!class_exists('SimpleXMLElement')) {
            $this->show_message_for_supervisor('Unable to get delivery points: SimpleXML extension is disabled');
            return null;
        }

        $response = $this->make_http_request(self::CDEK_PICKUP_POINTS_URL . '?citypostcode=' . $postal_code);
        if (!$response) {
            return null;
        }

        try {
            $points_list = new SimpleXMLElement($response);
        } catch (Exception $e) {
            $this->show_message_for_supervisor('Unable to get delivery point data: ' . $e->getMessage());
            return null;
        }

        $this->delivery_point_cache[$cache_key] = $delivery_points = new nc_netshop_delivery_point_external_collection();

        // WTF? СДЭК иногда начинает игнорировать параметр citypostcode (с корректным значением)
        // и отдаёт все свои ПВЗ во всей Вселенной.
        // Если такое происходит, придётся проверять совпадение по названию города (что менее надёжно)...
        $check_city_name = count($points_list->Pvz) > 400;

        $nc_core = nc_core::get_object();
        foreach ($points_list->Pvz as $point_data) {
            // "выдачи заказов нет"
            // - "Сортировочный центр (выдачи заказов нет)"
            // - "Сортировочный центр (приема/выдачи заказов нет)"
            if (strpos($point_data['Name'], 'выдачи заказов нет') !== false) {
                continue;
            }

            if ($point_data->WeightLimit[0] && $point_data->WeightLimit[0]['WeightMax'] < $total_weight_in_kg) {
                continue;
            }

            if ($check_city_name && (string)$point_data['City'] !== $this->data['to_city']) {
                continue;
            }

            $description =
                ($point_data['Note'] ? htmlspecialchars($point_data['Note']) . '<br>' : '') .
                "<a href='$point_data[Site]' target='_blank'>" .
                NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_POINT_LINK .
                "</a>";
            $phone = preg_replace('/\b(\d)(\d{3})(\d{3})(\d{2})(\d{2})\b/', "+$1 $2 $3-$4-$5", $point_data['Phone']);

            $point_properties = array(
                'id' => (string)$point_data['Code'],
                'name' => (string)$point_data['Name'],
                'description' => $description,
                'phones' => $phone,
                'location_name' => (string)$point_data['City'],
                'address' => (string)$point_data['Address'],
                'latitude' => (string)$point_data['coordY'],
                'longitude' => (string)$point_data['coordX'],
                'payment_on_delivery_cash' => ((string)$point_data['AllowedCod']) === 'есть',
                'payment_on_delivery_card' => ((string)$point_data['HaveCashless']) === 'есть',
                'enabled' => true,
            );

            $schedule = new nc_netshop_delivery_schedule();
            foreach ($point_data->WorkTimeY as $day) {
                list($time_from, $time_to) = explode('/', $day['periods'] . '/', 2);
                $interval = new nc_netshop_delivery_interval(array(
                    'day' . $day['day'] => true,
                    'time_from' => $time_from,
                    'time_to' => $time_to,
                ));
                $schedule->add($interval);
            }

            if (!$nc_core->NC_UNICODE) {
                $point_properties = $nc_core->utf8->array_utf2win($point_properties);
            }

            $point = new nc_netshop_delivery_point_external($point_properties);
            $point->set_schedule($schedule);
            $delivery_points->add($point);
        }

        return $this->delivery_point_cache[$cache_key];
    }

    /**
     * Создание заказа в СДЭК
     * @param nc_netshop_order $order
     */
    public function checkout(nc_netshop_order $order) {
        // Пока не реализовано.
        // Необходимо учесть, что к ID ПВЗ может быть добавлен '#' и ID
        // из $this->enabled_rate_types — его необходимо убрать.
        // (См. add_cdek_delivery_variant())
    }

}