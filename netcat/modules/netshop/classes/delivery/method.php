<?php

class nc_netshop_delivery_method extends nc_netshop_record_conditional {

    static protected $handler_data_cache = array();

    protected $primary_key = 'id';
    protected $properties = array(
        'id' => null,
        'catalogue_id' => null,
        'name' => '',
        'description' => '',
        'condition' => '',
        'handler_id' => null,
        'handler_settings' => null,
        'delivery_type' => nc_netshop_delivery::DELIVERY_TYPE_COURIER,
        'delivery_point_group' => '',
        'extra_charge_absolute' => null,
        'extra_charge_relative' => null,
        'minimum_delivery_days' => null,
        'maximum_delivery_days' => null,
        'shipment_days_of_week' => '', // '1,2,3,4,5,6,7'
        'shipment_time' => '', // '00:00'
        'priority' => 0,
        'enabled' => null,
        // свойства только для nc_netshop_delivery_method_variant (см. apply_method_properties_to_variants()):
        'external_id' => null, // изначальный (внешний) ID варианта доставки
        'are_method_properties_applied' => false, // к варианту доставки применены свойства способа доставки
    );

    protected $table_name = 'Netshop_DeliveryMethod';
    protected $mapping = array(
        'id' => 'DeliveryMethod_ID',
        'catalogue_id' => 'Catalogue_ID',
        'name' => 'Name',
        'description' => 'Description',
        'condition' => 'Condition',
        'handler_id' => 'ShopDeliveryService_ID',
        'handler_mapping' => 'ShopDeliveryService_Mapping',
        'handler_settings' => 'ShopDeliveryService_Settings',
        'delivery_type' => 'DeliveryType',
        'delivery_point_group' => 'DeliveryPointGroup',
        'extra_charge_absolute' => 'ExtraChargeAbsolute',
        'extra_charge_relative' => 'ExtraChargeRelative',
        'minimum_delivery_days' => 'MinimumDeliveryDays',
        'maximum_delivery_days' => 'MaximumDeliveryDays',
        'shipment_days_of_week' => 'ShipmentDaysOfWeek',
        'shipment_time' => 'ShipmentTime',
        'priority' => 'Priority',
        'enabled' => 'Checked',
    );

    protected $serialized_properties = array('handler_settings');

    /**
     * @var  bool|null|nc_netshop_delivery_service  экземпляр класса расчёта доставки
     * (false — не инициализирован, null — отсутствует)
     */
    protected $handler = false;

    /** @var  array   массив для хранения оценок стоимости и времени доставки */
    protected $estimations_cache = array();

    /** @var  nc_netshop_delivery_point_collection */
    protected $delivery_points;

    /** @var  string|null  город, для которого загружены $delivery_points */
    protected $loaded_delivery_points_location_name = null;

    /**
     * @var bool|int|float стоимость оплаты при получении [одинаковая для всех способов]
     *   (false — способ доставки не предполагает возможности оплаты при получении)
     */
    protected $payment_on_delivery_cost = false;

    /**
     * Возвращает название варианта и способа доставки (для шаблонов для
     * панели управления)
     *
     * @return string
     */
    public function get_variant_and_method_name() {
        return $this->get('name');
    }

    /**
     * Возвращает стоимость доставки указанного заказа. В возникновения ошибки
     * при расчёте стоимости возвращает NULL (следует отличать от 0, то есть
     * бесплатной доставки).
     *
     * @param nc_netshop_order $order
     * @return int|float|null
     */
    public function get_delivery_price(nc_netshop_order $order) {
        $estimate = $this->get_estimate($order);

        // error occurred:
        if (isset($estimate['error_code']) || !isset($estimate['price'])) {
            return null;
        }

        return $estimate['price'];
    }

    /**
     * Проверяет, зависит ли способ доставки от каких-либо данных, указываемых
     * при оформлении заказа.
     *
     * @return bool
     */
    public function depends_on_order_data() {
        return ($this->get('handler_id') || $this->has_condition_of_type('order'));
    }

    /**
     * Возвращает кол-во дней, через которое возможно начало доставки
     *
     * @return int $days_until_shipment
     */
    public function get_days_until_shipment() {
        // День начала доставки
        $shipment_time = $now = time();

        // Поиск ближайшего дня недели, когда возможна отправка
        $shipment_days = $this->get('shipment_days_of_week');
        if (!preg_match('/^[\d,]+$/', $shipment_days)) { $shipment_days = '1,2,3,4,5,6,7'; }
        $shipment_days = explode(",", $shipment_days);

        // Если отправка возможна в текущий день недели, но время, до которого
        // возможна отправка, прошло, прибавить день
        $the_train_is_off = in_array(date('N', $now), $shipment_days) &&
                            strtotime($this->get('shipment_time')) <= $now;
        // (для простоты здесь и далее возможные проблемы с переводом часов игнорируются)
        if ($the_train_is_off) { $shipment_time += 86400; }

        $security_counter = 30;
        while ($security_counter && !in_array(date('N', $shipment_time), $shipment_days)) {
            $shipment_time += 86400;
            $security_counter--;
        }

        return round(($shipment_time - $now) / 86400);
    }

    /**
     * Возвращает массив с оценкой стоимости и времени доставки заказа с указанными
     * параметрами.
     *
     * @param nc_netshop_order $order
     * @return nc_netshop_delivery_estimate
     */
    public function get_estimate(nc_netshop_order $order) {
        $order_data = $order->to_array();
        $cart_contents = $order->get_items();
        // cache responses for re-use
        $cache_key = sha1(serialize($order_data) . "\n/" . $cart_contents->get_hash());
        if ($this->estimations_cache[$cache_key]) {
            return $this->estimations_cache[$cache_key];
        }

        $result = array(
            'catalogue_id' => $this->get('catalogue_id'),
            'delivery_method_id' => $this->get_id(),
            'delivery_method_name' => $this->get('name'),
            'order_id' => $order->get_id(),
            'calculation_timestamp' => time(),
            'full_price' => null,
            'price' => null,
            'discount' => null,
            'min_days' => null,
            'max_days' => null,
            'error_code' => nc_netshop_delivery_estimate::ERROR_OK,
            'error' => '',
        );

        $netshop = $this->get_netshop();

        // Постоянная (независящая от службы доставки) часть стоимости
        $order_totals = $cart_contents->sum('TotalPrice');
        $delivery_cost = $this->get('extra_charge_absolute') +
                         $this->get('extra_charge_relative') * $order_totals / 100;

        $service_min_days = null;
        $service_max_days = null;

        $handler = $this->get_handler();
        if ($handler) {
            $handler->set_data($this->get_data_for_handler($order));
            $estimate = $handler->calculate_delivery();

            if ($handler->get_last_error_code() != nc_netshop_delivery_service::ERROR_OK) {
                // коды ошибок — одинаковые в estimate и service
                $result['error_code'] = $handler->get_last_error_code();
                $result['error'] = $handler->get_last_error();
            } else {
                $service_price = $netshop->convert_currency($estimate['price'], $estimate['currency']);
                $delivery_cost += $service_price;
                $service_min_days = isset($estimate['min_days']) ? $estimate['min_days'] : null;
                $service_max_days = isset($estimate['max_days']) ? $estimate['max_days'] : null;
            }
        }

        if (!$result['error_code']) {
            $delivery_cost = $netshop->round_price($delivery_cost);
            $result['full_price'] = $delivery_cost;

            // Учёт скидок на доставку
            if ($delivery_cost) {
                $original_condition_context_order = $netshop->get_condition_context()->get_order();
                $netshop->set_order_in_condition_context($order);

                $discount = $netshop->promotion->get_delivery_discount_sum($delivery_cost, $this->get_id());
                $delivery_cost = $delivery_cost - $discount;
                $result['discount'] = $discount;

                $netshop->set_order_in_condition_context($original_condition_context_order);
            }

            // Добавить в результат цену и отформатированную цену (с учётом скидок)
            $result['price'] = $delivery_cost;

            // Сроки доставки
            if ($service_min_days != null || is_numeric($this->get('minimum_delivery_days'))) {
                // Через сколько дней возможно начало доставки?
                $days_until_shipment = $this->get_days_until_shipment();

                // Теперь можно определиться с тем, когда может быть осуществлена доставка
                $min_days = intval($days_until_shipment +
                                   $this->get('minimum_delivery_days') +
                                   $service_min_days);

                $max_days = intval($days_until_shipment +
                                   $this->get('maximum_delivery_days') +
                                   ($service_max_days ? $service_max_days : $service_min_days));

                $result['min_days'] = $min_days;
                $result['max_days'] = max($min_days, $max_days);
            }

        }

        $this->estimations_cache[$cache_key] = new nc_netshop_delivery_estimate($result);

        return $this->estimations_cache[$cache_key];
    }

    /**
     * @return nc_netshop
     */
    protected function get_netshop() {
        return nc_netshop::get_instance($this->get('catalogue_id'));
    }

    /**
     * @return nc_netshop_delivery_service|null
     */
    protected function get_handler() {
        if ($this->handler === false) {
            $this->handler = $this->get_netshop()->delivery->get_delivery_service_by_id($this->get('handler_id'));
            if ($this->handler) {
                $handler_settings = $this->get('handler_settings');
                if ($handler_settings) {
                    $this->handler->set_settings($handler_settings);
                }

                // для способов доставки с автоматическим расчётом тип доставки
                // определяет эта служба
                $this->set('delivery_type', $this->handler->get_delivery_type());
            }
        }
        return $this->handler;
    }

    /**
     * Устанавливает данные заказа
     * (meh)
     *
     * @param nc_netshop_order $order
     * @return array
     */
    protected function get_data_for_handler(nc_netshop_order $order) {
        $cart_contents = $order->get_items();

        $cache_key = $cart_contents->get_hash() . ':' . sha1(serialize($order->to_array()));
        if (!isset(self::$handler_data_cache[$cache_key])) {
            $size = $cart_contents->get_package_size();

            self::$handler_data_cache[$cache_key] = array_merge(
                $this->map_handler_params($order),
                array(
                    'items' => $cart_contents,
                    'weight' => $cart_contents->get_field_sum('Weight') ?: 100,
                    'valuation' => $cart_contents->sum('TotalPrice'),
                    'size1' => $size[0],
                    'size2' => $size[1],
                    'size3' => $size[2],
                )
            );
        }

        return self::$handler_data_cache[$cache_key];
    }

    /**
     * Завершение оформления заказа
     * @param nc_netshop_order $order
     */
    public function checkout(nc_netshop_order $order) {
        $handler = $this->get_handler();

        if (!$handler) {
            return;
        }

        $handler->set_data($this->get_data_for_handler($order));

        $handler->checkout($order);
    }

    /**
     * @param nc_netshop_order $order
     * @return array
     */
    protected function map_handler_params(nc_netshop_order $order) {
        $result = array();

        $mapping = $this->get('handler_mapping');
        if (!$mapping) {
            return $result;
        }

        $mapping = @json_decode($mapping, true);
        if (!is_array($mapping)) {
            return $result;
        }

        /** @var nc_netshop $netshop */
        $netshop = nc_netshop::get_instance($order->get_catalogue_id());  // экземпляр nc_netshop для текущего сайта

        $order_component = nc_core::get_object()->get_component($netshop->get_setting('OrderComponentID'));
        $shop_fields = nc_netshop_admin_helpers::get_shop_fields();

        foreach ($mapping as $to => $from) {
            $value = null;
            list ($from_source, $from_field) = explode("_", $from, 2);

            if ($from_source === 'shop') {
                $value = $netshop->get_setting($from_field);
                if (isset($shop_fields[$from_field]['classificator'])) {
                    $value = nc_get_list_item_name($shop_fields[$from_field]['classificator'], $value);
                }
            } elseif ($from_source === 'order') {
                $order_field = $order_component->get_field($from_field);
                $value = $order->get($order_field['name']);

                if ($order_field['type'] == NC_FIELDTYPE_SELECT) {
                    $value = nc_get_list_item_name($order_field['table'], $value);
                }
            }
            $result[$to] = $value;
        }

        $result = $this->resolve_address_fields('from', $result, $netshop);
        $result = $this->resolve_address_fields('to', $result, $netshop);

        return $result;
    }

    /**
     * @param $prefix
     * @param array $result
     * @param nc_netshop $netshop
     * @return array
     */
    protected function resolve_address_fields($prefix, array $result, nc_netshop $netshop) {
        if (!empty($result[$prefix . '_city'])) {
            $location_data = $netshop->location->find_locations($result[$prefix . '_city']);
            if (!empty($location_data[0]['is_exact_match'])) {
                $location_data = $location_data[0];
                $result[$prefix . '_location_data'] = $location_data;
                $result[$prefix . '_city'] = $location_data['locality_name'];
                // country_name → from_country, region_name → from_region, district_name → from_district
                // (same for to_country, to_region, to_district)
                foreach (array('country', 'region', 'district') as $type) {
                    $result_property = $prefix . '_' . $type;
                    if (!isset($result[$result_property])) {
                        $result[$result_property] = $location_data->get($type . '_name');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Возвращает все возможные варианты доставки
     *
     * @param nc_netshop_order $order
     * @return nc_netshop_delivery_method_collection
     */
    public function get_variants(nc_netshop_order $order) {
        $handler = $this->get_handler();
        if ($handler) {
            $handler->set_data($this->get_data_for_handler($order));
            $variants = $handler->get_variants($this);
            $this->apply_method_properties_to_variants($variants);
            return $variants;
        } else {
            return new nc_netshop_delivery_method_collection(array($this));
        }
    }

    /**
     * Применяет настройки способа доставки к вариантам доставки, полученным
     * от класса расчёта доставки (меняет объекты в коллекции).
     *
     * @param nc_netshop_delivery_method_collection $variants
     */
    protected function apply_method_properties_to_variants(nc_netshop_delivery_method_collection $variants) {
        if (!$variants->count()) {
            return;
        }

        $method_id = $this->get_id();
        $site_id = $this->get('catalogue_id');
        $shipment_days_of_week = $this->get('shipment_days_of_week');
        $shipment_time = $this->get('shipment_time');
        $priority = $this->get('priority');
        $extra_charge_absolute = $this->get('extra_charge_absolute');
        $extra_charge_relative = $this->get('extra_charge_relative');
        $minimum_delivery_days = $this->get('minimum_delivery_days');
        $maximum_delivery_days = $this->get('maximum_delivery_days');

        /** @var nc_netshop_delivery_method_variant $variant */
        foreach ($variants as $variant) {
            if (!($variant instanceof nc_netshop_delivery_method_variant) || $variant->get('are_method_properties_applied')) {
                continue;
            }

            $original_variant_id = $variant->get_id() ?: md5(serialize($variant->to_array()));
            $full_variant_id = "$method_id:$original_variant_id";
            $variant->set_values(array(
                'id' => $full_variant_id,
                'external_id' => $original_variant_id,
                'method_id' => $method_id,
                'catalogue_id' => $site_id,
                'condition' => '',
                'extra_charge_absolute' => $variant->get('extra_charge_absolute') + $extra_charge_absolute,
                'extra_charge_relative' => $variant->get('extra_charge_relative') + $extra_charge_relative,
                'minimum_delivery_days' => $variant->get('minimum_delivery_days') + $minimum_delivery_days,
                'maximum_delivery_days' => $variant->get('maximum_delivery_days') + $maximum_delivery_days,
                'shipment_days_of_week' => $shipment_days_of_week,
                'shipment_time' => $shipment_time,
                'priority' => $priority,
                'enabled' => true,
                'are_method_properties_applied' => true,
            ));

            if ($variant->delivery_points) {
                foreach ($variant->delivery_points as $delivery_point) {
                    if ($delivery_point instanceof nc_netshop_delivery_point_external) {
                        $delivery_point_id = $delivery_point->get_id() ?: md5(serialize($delivery_point->to_array()));
                        $delivery_point->set('external_id', $delivery_point_id);
                        $delivery_point->set_id("$full_variant_id:$delivery_point_id");
                    }
                }
            }
        }
    }

    /**
     * @param $variant_id
     * @param nc_netshop_order $order
     * @return nc_netshop_delivery_method_variant|null
     */
    public function get_variant($variant_id, nc_netshop_order $order) {
        /** @var nc_netshop_delivery_method_variant $result */
        $result = $this->get_variants($order)->first('external_id', $variant_id);
        return $result;
    }

    /**
     * Возвращает тип доставки способом (почтовая/курьерская/до пункта выдачи)
     * @return string константа nc_netshop_delivery::DELIVERY_TYPE_*
     */
    public function get_delivery_type() {
        $this->get_handler(); // установит 'delivery_type' из класса расчёта доставки, если он есть
        return $this->get('delivery_type');
    }

    /**
     * Возвращает пункт выдачи с указанным идентификатором
     *
     * @param $point_id
     * @return nc_netshop_delivery_point|null
     */
    public function get_delivery_point($point_id) {
        if ($this->delivery_points && $this->get_delivery_points()->count()) {
            // Все пункты выдачи загружены в $this->delivery_points
            return $this->get_delivery_points()->first('id', $point_id);
        }

        $handler = $this->get_handler();
        if ($handler) {
            // возможно, служба расчёта доставки может вернуть пункт по ID
            return $handler->get_delivery_point($point_id);
        } else {
            // пробуем загрузить информацию о собственном пункте выдачи
            try {
                return new nc_netshop_delivery_point_local($point_id);
            } catch (Exception $e) {
                return null; // точка доставки не существует
            }
        }
    }


    /**
     * Возвращает коллекцию с пунктами выдачи заказа для указанного города
     * с учётом группы пунктов выдачи, указанной в настройках способа доставки.
     *
     * Фильтрация по городу производится только для пунктов выдачи, которые заданы
     * в модуле (не производится для пунктов выдачи, которые были установлены
     * классом автоматического расчёта доставки).
     *
     * @param string|null $location_name название населённого пункта.
     *   Если нестрого равно false (например, null), возвращает пункты выдачи для всех населённых пунктов.
     * @return nc_netshop_delivery_point_collection
     */
    public function get_delivery_points($location_name = null) {
        $are_all_delivery_points_loaded =
            $this->delivery_points && !$this->loaded_delivery_points_location_name;

        $are_same_delivery_points_loaded =
            $are_all_delivery_points_loaded ||
            ($this->delivery_points && $this->loaded_delivery_points_location_name === $location_name);

        if (!$this->delivery_points || !$are_same_delivery_points_loaded) {
            $handler = $this->get_handler();
            if ($handler) {
                // возможно, служба расчёта доставки умеет возвращать список пунктов (или вернёт null)
                $this->delivery_points = $handler->get_delivery_points($location_name);
            }

            if (!$this->delivery_points) {
                if ($this->get('delivery_type') == nc_netshop_delivery::DELIVERY_TYPE_PICKUP) {
                    $this->delivery_points = $this->load_delivery_points($location_name);
                } else {
                    $this->delivery_points = new nc_netshop_delivery_point_local_collection();
                }
            }
        }

        $is_local = $this->delivery_points instanceof nc_netshop_delivery_point_local_collection;
        if ($is_local && $location_name && $are_all_delivery_points_loaded) {
            return $this->delivery_points->where('location_name', $location_name);
        }

        return $this->delivery_points;
    }

    /**
     * Проверяет, есть ли пункты выдачи заказов у способа доставки для указанного
     * населённого пункта.
     *
     * @param string|null $location_name название населённого пункта
     *   Если нестрого равно false (например, null), возвращает пункты выдачи для всех населённых пунктов.
     * @return bool
     */
    public function has_delivery_points($location_name = null) {
        return count($this->get_delivery_points($location_name)) > 0;
    }

    /**
     * @param string|null $location_name
     * @return nc_netshop_delivery_point_collection
     */
    protected function load_delivery_points($location_name) {
        $site_id = (int)$this->get('catalogue_id');
        $query = "SELECT * FROM `%t%` WHERE `Catalogue_ID` = $site_id AND `Checked` = 1";
        if ($location_name) {
            $query .= " AND `LocationName` = '" . nc_db()->escape($location_name) . "'";
        }
        $group = $this->get('delivery_point_group');
        if (strlen($group)) {
            $query .= " AND `Group` = '" . nc_db()->escape($group) . "'";
        }

        $this->loaded_delivery_points_location_name = $location_name;

        return nc_record_collection::load('nc_netshop_delivery_point_local', $query);
    }

    /**
     * Устанавливает пункты выдачи заказа
     * @param nc_netshop_delivery_point_collection $delivery_points
     */
    public function set_delivery_points(nc_netshop_delivery_point_collection $delivery_points) {
        $this->delivery_points = $delivery_points;
    }

    /**
     * Проверяет, есть ли координаты хотя бы у одного пункта выдачи заказа
     * @return bool
     */
    public function has_delivery_points_with_coordinates() {
        return $this->get_delivery_points()->any('latitude', '', '!=');
    }

    /**
     * @return bool|float|int
     */
    public function get_payment_on_delivery_cost() {
        return $this->payment_on_delivery_cost;
    }

    /**
     * @param bool|float|int $payment_on_delivery_cost
     */
    public function set_payment_on_delivery_cost($payment_on_delivery_cost) {
        $this->payment_on_delivery_cost = $payment_on_delivery_cost;
    }

}