<?php

class nc_netshop_delivery_service_russianpost extends nc_netshop_delivery_service {

    /** @var string название службы */
    protected $name = NETCAT_MODULE_NETSHOP_DELIVERY_RUSSIANPOST;

    /** @var string тип доставки */
    protected $delivery_type = nc_netshop_delivery::DELIVERY_TYPE_POST;

    /** @var array поля, которым нужны соответствия */
    protected $mapped_fields = array(
        'from_city' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_FROM_CITY,
        'to_zipcode' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_TO_ZIP_CODE,
        'to_city' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_TO_CITY,
    );

    /** @var int максимальный вес посылки (в граммах) */
    protected $max_weight = 50000;

    // название свойств в ответе для получения данных о стоимости и сроках
    protected $request_posting_kind = 'PARCEL';
    protected $request_way_forward = 'EARTH';
    protected $response_time_range_property = 'deliveryTimeRange';
    protected $response_time_property = 'deliveryTime';
    protected $response_min_time_property = 'deliveryTime';
    protected $response_max_time_property = 'deliveryTime';

    /**
     * Рассчитать стоимость посылки.
     * При успешном выполнении возвращается массив:
     * array(
     *     'price' => стоимость доставки,
     *     'currency' => 'RUB',
     *     'min_days' => минимальный срок доставки
     *     'max_days' => максимальный срок доставки
     * )
     *
     * При ошибке возвращается null, ошибку можно получить из $this->get_last_error()
     *
     * @return array|null
     */
    public function calculate_delivery() {
        $nc_core = nc_Core::get_object();

        $delivery_data = $this->data;
        if (!$nc_core->NC_UNICODE) {
            $delivery_data = $nc_core->utf8->array_win2utf($delivery_data);
        }

        $weight = ceil($delivery_data['weight']); // вес в граммах

        if ($weight <= 0 || $weight > $this->max_weight) {
            $this->last_error = NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_INCORRECT_WEIGHT;
            $this->last_error_code = self::ERROR_WRONG_WEIGHT;
            return null;
        }

        $valuation = $delivery_data['valuation'] ? ceil($delivery_data['valuation']) : 0;

        // Откуда посылаем (только Россия)
        $from_postal_code = null;
        $from_location_data = $delivery_data['from_location_data'];
        if ($from_location_data) {
            $from_postal_code = nc_netshop_location_provider_russianpost::get_first_postal_code($delivery_data['from_location_data']);
        }
        
        
        if (!$from_postal_code) {
            $this->last_error_code = self::ERROR_WRONG_SENDER;
            $this->last_error = NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_INCORRECT_SENDER_ADDRESS;
            return null;
        }

        // Куда посылаем
        $to_postal_code = $delivery_data['to_zipcode'];
        $to_location_data = $delivery_data['to_location_data'];
        $to_country = $delivery_data['to_country'];
        $to_country = mb_strtoupper($to_country, 'utf-8');

        $to_country_code = null;
        $international =
            $delivery_data['to_country'] &&
            $to_country !== 'РОССИЯ' &&
            $to_country !== 'РОССИЙСКАЯ ФЕДЕРАЦИЯ';

        if ($to_location_data) {
            if ($international) {
                $to_country_code = $to_location_data['country_code'];
            } else {
                $to_postal_code = nc_netshop_location_provider_russianpost::get_first_postal_code($to_location_data);
            }
        }

        if (($international && !$to_country_code) || (!$international && !$to_postal_code)) {
            $this->last_error_code = self::ERROR_WRONG_RECIPIENT;
            $this->last_error = NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_INCORRECT_RECIPIENT_ADDRESS;
            return null;
        }

        $calculation_entity = array(
            'postingType' => $international ? 'MPO' : 'VPO',
            'zipCodeFrom' => $from_postal_code,
            'zipCodeTo' => $to_postal_code,
            'postalCodesFrom' => array($from_postal_code),
            'postalCodesTo' => array($to_postal_code),
            'weight' => $weight,
            'wayForward' => $this->request_way_forward,
            'postingKind' => $this->request_posting_kind,
            'postingCategory' => 'WITH_DECLARED_VALUE', // без объявленной ценности — 'ORDINARY',
            'parcelKind' => 'STANDARD',
            'declaredValue' => $valuation,
        );

        if ($international) {
            $calculation_entity['countryTo'] = $to_country_code;
        }

        $request = array(
            'calculationEntity' => array(
                'origin' =>
                    array(
                        'country' => 'Россия',
                        'region' => $delivery_data['from_region'],
                        'district' => $delivery_data['from_district'],
                        'city' => $delivery_data['from_city'],
                    ),
                'destination' =>
                    array(
                        'country' => $international ? $delivery_data['to_country'] : 'Россия',
                        'region' => $delivery_data['to_region'],
                        'district' => $delivery_data['to_district'],
                        'city' => $delivery_data['to_city'],
                    ),
                'sendingType' => 'PACKAGE'
            ),
            'costCalculationEntity' => $calculation_entity,
            'minimumCostEntity' =>
                array(
                    'standard' => $calculation_entity,
                    'firstClass' => $calculation_entity,
                    'ems' => $calculation_entity,
                ),
            'productPageState' =>
                array(
                    'cashOnDelivery' => false,
                    'ems' => false,
                    'rapid' => false,
                    'international' => $international,
                    'standard' => true,
                    'fromCity' => '',
                    'fromCountry' => '',
                    'fromDistrict' => '',
                    'fromRegion' => '',
                    'toCity' => '',
                    'toCountry' => '',
                    'toDistrict' => '',
                    'toRegion' => '',
                    'weight' => $weight,
                    'showAsKg' => false,
                    'cost' => 0,
                    'insuranceSum' => null,
                    'cashOnDeliverySum' => null,
                    'mainType' => 'standardParcel',
                    'sizeType' => 'items',
                    'emsTerm' => '',
                    'firstClassTerm' => '',
                    'standardTerm' => '',
                    'productType' => 'PARCEL',
                    'printSummary' => true,
                    'sourceHasCourier' => true,
                    'destinationHasCourier' => false,
                    'costDetailsColumns' => null,
                    'costDetailsSummary' => array(''),
                    'costDetailsRows' => array(array('', '0.00')),
                    'costDetailsSummaryCostNds' => '0,00',
                    'costDetailsSummaryCostMark' => null,
                ),
        );

        $url = 'https://www.pochta.ru/portal-portlet/delegate/calculator/v1/api/delivery.time.cost.get';
        $headers = array(
            'Content-Type' => 'application/json',
            'Referer' => 'https://www.pochta.ru/parcels',
        );

        $result = $this->make_http_request($url, nc_array_json($request), $headers);

        if (!$result) {
            $this->last_error_code = self::ERROR_CANNOT_CONNECT_TO_GATE;
            $this->last_error = NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_NO_REMOTE_SERVER_RESPONSE;
            return null;
        }

        $response = json_decode($result, JSON_OBJECT_AS_ARRAY);
        if (!$response || !isset($response['data']['costEntity']['cost']) || !$response['data']['costEntity']['cost']) {
            $this->last_error_code = self::ERROR_GATE_ERROR;
            $this->last_error = NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_INCORRECT_REMOTE_SERVER_RESPONSE;
            return null;
        }

        if (!empty($response['data']['timeEntity'][$this->response_time_range_property])) {
            $time_range = $response['data']['timeEntity'][$this->response_time_range_property];
        }
        else if (!empty($response['data']['timeEntity'][$this->response_time_property])) {
            // для стандартной доставки deliveryTime иногда содержит диапазон, deliveryTimeRange всегда пустой
            $time_range = $response['data']['timeEntity'][$this->response_time_property];
        }
        else {
            $time_range = null;
        }

        if ($time_range && preg_match('/\D+/', $time_range)) {
            list($min_days, $max_days) = preg_split('/\D+/', $time_range);
        }
        else {
            $min_days = $response['data']['timeEntity'][$this->response_min_time_property] ?: $time_range;
            $max_days = $response['data']['timeEntity'][$this->response_max_time_property] ?: $time_range;
        }

        return array(
            'price' => $response['data']['costEntity']['cost'],
            'currency' => 'RUB',
            'min_days' => $min_days,
            'max_days' => $max_days,
        );
    }

    /**
     * Возврат HTML кода сформированного
     * бланка посылки
     *
     * @return string
     */
    public function print_package_form() {
        $forms = nc_netshop::get_instance()->forms->get_objects();

        ob_start();
        $forms->russianpost_package->template($this->data);
        return ob_get_clean();
    }

    /**
     * Возврат HTML кода сформированного
     * бланка наложенного платежа
     *
     * @return string
     */
    public function print_cash_on_delivery_form() {
        $forms = nc_netshop::get_instance()->forms->get_objects();

        ob_start();
        $forms->russianpost_cash_on_delivery->template($this->data);
        return ob_get_clean();
    }

    /**
     * Возврат информации по точкам
     * следования посылки
     *
     * @return array|null
     */
    public function get_tracking_information() {
        return null;
    }

}