<?php

class TemplateReplacer
{
    public $regs = [
            'main' => [
                    'id' => 1,
                    'city_pred' => 'Набережные Челны',
                    'city_vin' => 'Набережных Челнах',
                    'city_imen' => 'Набережные Челны',
            ],
            'spb' => [
                    'id' => 7,
                    'city_pred' => 'Санкт-Петербург',
                    'city_vin' => 'Санкт-Петербурге',
                    'city_imen' => 'Санкт-Петербург',
            ],
            'msk' => [
                    'id' => 14,
                    'city_pred' => 'Москву',
                    'city_vin' => 'Москве',
                    'city_imen' => 'Москва',
            ],
            'kzn' => [
                    'id' => 5,
                    'city_pred' => 'Казань',
                    'city_vin' => 'Казани',
                    'city_imen' => 'Казань',
            ],
            'smr' => [
                    'id' => 8,
                    'city_pred' => 'Самару',
                    'city_vin' => 'Самаре',
                    'city_imen' => 'Самара',
            ],
            'ufa' => [
                    'id' => 11,
                    'city_pred' => 'Уфу',
                    'city_vin' => 'Уфе',
                    'city_imen' => 'Уфа',
            ],
            'tyumen' => [
                    'id' => 9,
                    'city_pred' => 'Тюмень',
                    'city_vin' => 'Тюмени',
                    'city_imen' => 'Тюмень',
            ],
            'irk' => [
                    'id' => 12,
                    'city_pred' => 'Иркутск',
                    'city_vin' => 'Иркутске',
                    'city_imen' => 'Иркутск',
            ],
            'ekb' => [
                    'id' => 13,
                    'city_pred' => 'Екатеринбург',
                    'city_vin' => 'Екатеринбурге',
                    'city_imen' => 'Екатеринбург',
            ],
            'barn' => [
                    'id' => 18,
                    'city_pred' => 'Барнаул',
                    'city_vin' => 'Барнауле',
                    'city_imen' => 'Барнаул',
            ],
            'ast' => [
                    'id' => 17,
                    'city_pred' => 'Астрахань',
                    'city_vin' => 'Астрахани',
                    'city_imen' => 'Астрахань',
            ],
            'perm' => [
                    'id' => 19,
                    'city_pred' => 'Пермь',
                    'city_vin' => 'Перми',
                    'city_imen' => 'Пермь',
            ],
            'tomsk' => [
                    'id' => 20,
                    'city_pred' => 'Томск',
                    'city_vin' => 'Томске',
                    'city_imen' => 'Томск',
            ],
            'yl' => [
                    'id' => 21,
                    'city_pred' => 'Ульяновск',
                    'city_vin' => 'Ульяновске',
                    'city_imen' => 'Ульяновск',
            ],
            'yar' => [
                    'id' => 22,
                    'city_pred' => 'Ярославль',
                    'city_vin' => 'Ярославле',
                    'city_imen' => 'Ярославль',
            ],
            'chelyabinsk' => [
                    'id' => 24,
                    'city_pred' => 'Челябинск',
                    'city_vin' => 'Челябинске',
                    'city_imen' => 'Челябинск',
            ],
            'tol' => [
                    'id' => 25,
                    'city_pred' => 'Тольятти',
                    'city_vin' => 'Тольятти',
                    'city_imen' => 'Тольятти',
            ],
            'rzn' => [
                    'id' => 26,
                    'city_pred' => 'Рязань',
                    'city_vin' => 'Рязани',
                    'city_imen' => 'Рязань',
            ],
            'saratov' => [
                    'id' => 27,
                    'city_pred' => 'Саратове',
                    'city_vin' => 'Саратове',
                    'city_imen' => 'Саратов',
            ],
            'rostov' => [
                    'id' => 28,
                    'city_pred' => 'Ростов',
                    'city_vin' => 'Ростове',
                    'city_imen' => 'Ростов',
            ],
            'volgograd' => [
                    'id' => 29,
                    'city_pred' => 'Волгоград',
                    'city_vin' => 'Волгограде',
                    'city_imen' => 'Волгоград',
            ],
            'voron' => [
                    'id' => 30,
                    'city_pred' => 'Воронеж',
                    'city_vin' => 'Воронеже',
                    'city_imen' => 'Воронеж',
            ],
            'izh' => [
                    'id' => 31,
                    'city_pred' => 'Ижевск',
                    'city_vin' => 'Ижевске',
                    'city_imen' => 'Ижевск',
            ],
            'kemerovo' => [
                    'id' => 32,
                    'city_pred' => 'Кемерово',
                    'city_vin' => 'Кемерово',
                    'city_imen' => 'Кемерово',
            ],
            'kirov' => [
                    'id' => 33,
                    'city_pred' => 'Киров',
                    'city_vin' => 'Кирове',
                    'city_imen' => 'Киров',
            ],
            'krasnodar' => [
                    'id' => 34,
                    'city_pred' => 'Краснодар',
                    'city_vin' => 'Краснодаре',
                    'city_imen' => 'Краснодар',
            ],
            'krasnoyarsk' => [
                    'id' => 35,
                    'city_pred' => 'Красноярск',
                    'city_vin' => 'Красноярске',
                    'city_imen' => 'Красноярск',
            ],
            'nnov' => [
                    'id' => 36,
                    'city_pred' => 'Нижнем Новгород',
                    'city_vin' => 'Нижнем Новгороде',
                    'city_imen' => 'Нижний Новгород',
            ],
            'novokz' => [
                    'id' => 37,
                    'city_pred' => 'Новокузнецк',
                    'city_vin' => 'Новокузнецке',
                    'city_imen' => 'Новокузнецк',
            ],
            'novosib' => [
                    'id' => 38,
                    'city_pred' => 'Новосибирск',
                    'city_vin' => 'Новосибирске',
                    'city_imen' => 'Новосибирск',
            ],
            'omsk' => [
                    'id' => 39,
                    'city_pred' => 'Омск',
                    'city_vin' => 'Омске',
                    'city_imen' => 'Омск',
            ],
            'penza' => [
                    'id' => 40,
                    'city_pred' => 'Пензу',
                    'city_vin' => 'Пензе',
                    'city_imen' => 'Пенза',
            ],
            'orenburg' => [
                    'id' => 41,
                    'city_pred' => 'Оренбург',
                    'city_vin' => 'Оренбурге',
                    'city_imen' => 'Оренбург',
            ],
    ];
    public $regionName;
    private $transliteratedWords;

    /**
     * @param $regionId
     */
    public function __construct(
            $regionId
    ) {
        $this->regionName = $this->getRegName($regionId);
        $tmp_arr = [];
        $fileData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/export/translit.txt');
        $fileData = mb_split("\n", mb_convert_encoding($fileData, 'utf-8', 'windows-1251'));

        array_pop($fileData);
        foreach ($fileData as $row => $value) {
            $temp = explode("\t", $value);
            if ($temp[0] !== '') {
                $tmp_arr[trim($temp[0])] = trim($temp[1]);
            }

        }

        $this->transliteratedWords = $tmp_arr;
    }
    /**
     * @param int $regionId
     * @return int|string
     */
    public function getRegName(int $regionId)
    {
        foreach ($this->regs as $regName => $regData) {
            if ($regData['id'] === $regionId) {
                return $regName;
            }
        }
    }

    /**
     * @param $string
     * @param $markName
     * @param $modelName
     * @return string
     */
    public function templateReplace($string, $markName, $modelName): string
    {
        $translit = [
                '{марка транслит}' => strtr($markName, $this->transliteratedWords),
                '{марка}' => $markName,
                '{город в предложном падеже}' => $this->regs[$this->regionName]['city_pred'],
                '{город в винительном падеже}' => $this->regs[$this->regionName]['city_vin'],
                '{город в именительном падеже}' => $this->regs[$this->regionName]['city_imen'],
                '{модель}' => '',
                '{модель транслит}' => '',
        ];

        if ($modelName !== null) {
        	$translit['{модель}'] = $modelName;
        	$translit['{модель транслит}'] = strtr($modelName, $this->transliteratedWords);
        }

        return strtr($string, $translit);
    }
}