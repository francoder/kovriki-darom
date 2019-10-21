<?php


class nc_netshop_delivery_service_kit extends nc_netshop_delivery_service
{


    /** @var string название службы */
    protected $name = NETCAT_MODULE_NETSHOP_DELIVERY_KIT;

    /** @var string тип доставки */
    protected $delivery_type = nc_netshop_delivery::DELIVERY_TYPE_POST;

    /**
     * Поля, которым нужны соответствия
     * @var array
     */
    protected $mapped_fields = array(
        'to_city' => NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_TO_CITY,
    );



    /**
     * Рассчитать стоимость посылки.
     * При успешном выполнении возвращается массив:
     * array(
     *     'price' => стоимость доставки,
     *     'currency' => валюта стоимости доставки
     *     'min_days' => минимальное количество дней на доставку
     *     'max_days' => максимальное количество дней на доставку
     * )
     *
     * При ошибке возвращается null
     *
     * @return array|null
     */
    public function calculate_delivery()
    {
        $adr  = $this->data['to_city'];
        $city = "";

        $ch = curl_init();

        $url = 'https://tk-kit.ru/API.1.1?f=get_city_list&token=r0y1eyg_lJkwH90z4dGTm3wvqHqPbrex';

        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
        ));

        $out = curl_exec($ch);

        $res = json_decode($out, true);

        foreach ($res["CITY"] as $key => $value) {

            $value['NAME'] = explode('(', $value['NAME']);
            $value['NAME'] = $value['NAME'][0];
            if (stristr($value['NAME'], $adr) === false) {

            } else {
                $city = $value;
            }
        }

        $ch = curl_init();

        $url = 'https://tk-kit.ru/API.1.1?f=price_order&I_DELIVER=0&I_PICK_UP=1&WEIGHT=30&VOLUME=0.6&SLAND=RU&SZONE=0000001610&SCODE=160000200000&SREGIO=16&RLAND=RU&RZONE='.$city["TZONEID"].'&WEIGHT=2&RCODE='.$city["ID"].'&RREGIO='.$city["REGION"].'&KWMENG=1&LENGTH=80&WIDTH=60&HEIGHT=10&GR_TYPE=&LIFNR=&PRICE=&WAERS=RUB&token=r0y1eyg_lJkwH90z4dGTm3wvqHqPbrex';

        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
        ));

        $out = curl_exec($ch);
        $res = json_decode($out, true);


        if (($res['PRICE']['TRANSFER'] !== 0) && ($city["ID"] !== "160000200000")) {
            $result['price'] = $res['PRICE']['TRANSFER'];

            $result['days'] = $res['DAYS'];
            if ($res['DAYS'] == null) {
                $result['days'] = "в течении ";

            }
        } else {
            $result['can'] = "no";
        }

        if($result['can'] == 'no') {
            $this->last_error_code = self::ERROR_WRONG_RECIPIENT;
            $this->last_error = NETCAT_MODULE_NETSHOP_DELIVERY_METHOD_SERVICE_INCORRECT_RECIPIENT_ADDRESS;
            return null;
        }

        return array(
            'price' => $result['price'],
            'currency' => 'RUR',
            'min_days' => '',
            'max_days' => ''
        );
    }
}