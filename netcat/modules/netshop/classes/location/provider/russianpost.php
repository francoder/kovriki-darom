<?php

/**
 * Поиск населённых пунктов в России по базе данных почтовых индексов
 *
 * Уникальные названия населённых пунктов формируются следующим образом:
 *  — если населённый пункт с таким названием один — только название н. п.: «Москва»
 *  — если в РФ несколько н. п. с таким названием — добавляется область: «Иваново, Ивановская область»
 *  — если в области несколько н. п. с таким названием — добавляется район: «Иваново, Псковская область, Великолукский район»
 */
class nc_netshop_location_provider_russianpost extends nc_netshop_location_provider {

    const WEIGHT_DEFAULT = 100;           // начальный вес
    const WEIGHT_POPULATION_BOOST = 10;   // + к весу для городов с населением > 100K
    const WEIGHT_LOCALITY_MATCH = 50;     // + к весу при полном совпадении названия населённого пункта

    const MAX_RESULTS = 100; // максимальное количество результатов

    static protected $converted = false;
    static protected $country_name = 'Россия';
    static protected $country_code = '643';
    static protected $ignored_name_prefixes =
        '/^(?:
            г[.\s]+|гор[.\s]+|город\s+|
            п\.?\s*г\.?\s*т\.?\s*|
            п[.\s]+|пос[.\s]+|поселок\s+|
            д[.\s]+|дер[.\s]+|деревня\s+|
            c[.\s]+|село\s+|хутор\s+|станица\s+|станция\s+|ст[.\s]+
         )/xi';

    /** @var bool всегда добавлять область к названию городов < 100K */
    protected $add_region_for_smaller_localities = true;

    /**
     * @param nc_netshop_location_data $location_data
     * @return int|null
     */
    static public function get_first_postal_code(nc_netshop_location_data $location_data) {
        if ($location_data['country_name'] !== self::$country_name) {
            return null;
        }

        if ($location_data['provider'] === __CLASS__) {
            $locality_id = (int)$location_data['locality_id'];
        } else {
            $rows = self::query_database(
                $location_data['region_name'],
                $location_data['district_name'],
                $location_data['locality_name']
            );
            if (!isset($rows[0]['locality_id'])) {
                return null;
            }
            $locality_id = $rows[0]['locality_id'];
        }

        return nc_db()->get_var(
            "SELECT `Russianpost_Code`
               FROM `Russianpost_Code` 
              WHERE `Russianpost_Locality_ID` = $locality_id 
              LIMIT 1"
        );
    }

    /**
     *
     */
    public function __construct() {
        if (!self::$converted && !nc_core::get_object()->NC_UNICODE) {
            $nc_core = nc_core::get_object();
            self::$country_name = $nc_core->utf8->utf2win(self::$country_name);
            self::$ignored_name_prefixes = $nc_core->utf8->utf2win(self::$ignored_name_prefixes);
        }
        self::$converted = true;
    }

    /**
     * @param string $search_string
     * @return nc_netshop_location_data_collection
     */
    public function find_locations($search_string) {
        $search_parts = preg_split('/\s*,\s*/', trim($search_string));
        $locality = $search_parts[0];
        $region = nc_array_value($search_parts, 1);
        $district = nc_array_value($search_parts, 2);

        $matches = self::query_database($region, $district, $locality, $this->add_region_for_smaller_localities);

        $result = new nc_netshop_location_data_collection();
        foreach ($matches as $data) {
            $locality = new nc_netshop_location_data($data);
            if ($data['name'] === $search_string || ($data['uniqueness_level'] == 0 && $data['locality_name'] === $search_string)) {
                $locality->set('is_exact_match', true);
            }
            $result->add($locality);
        }

        return $result;
    }

    /**
     * @param $region
     * @param $district
     * @param $locality
     * @param bool $add_region_for_smaller_localities
     * @return array
     */
    static protected function query_database($region, $district, $locality, $add_region_for_smaller_localities = true) {
        $db = nc_db();
        $region   = $region   ? $db->escape($region)   : null;
        $district = $district ? $db->escape($district) : null;

        // Убираем сокращения типа «г.», «дер.» и т. п.
        // Допустимые префиксы (входят в состав названий): «Поселок» и «Станция»
        // (но проверка, возможно ли теоретическое совпадение по $filtered_locality, не даёт общего ускорения выполнения запроса)
        $filtered_locality = $locality;
        $filtered_locality = preg_replace(self::$ignored_name_prefixes, '', $filtered_locality);
        $filtered_locality = $db->escape($filtered_locality);

        $locality = $db->escape($locality);

        $name_with_region = "CONCAT_WS(', ', l.`Locality_Name`, r.`Region_Name`)";
        $full_name =
            "CASE l.`UniquenessLevel`" .
            " WHEN 1 THEN $name_with_region" .
            " WHEN 2 THEN CONCAT_WS(', ', l.`Locality_Name`, r.`Region_Name`, d.`District_Name`)" .
            " ELSE " . ($add_region_for_smaller_localities
                            ? "IF(l.`Boost` = 1, l.`Locality_Name`, $name_with_region)"
                            : "r.`Locality_Name`") .
            " END";
        
        $weight =
            self::WEIGHT_DEFAULT .
            " + l.`Boost` * " . self::WEIGHT_POPULATION_BOOST .
            " + IF(l.`Locality_Name` = '$locality', " . self::WEIGHT_LOCALITY_MATCH . ", 0)";

        $query =
            "SELECT l.`Russianpost_Locality_ID` AS `locality_id`,
                    '" . self::$country_name . "' AS `country_name`,
                    '" . self::$country_code . "' AS `country_numeric_code`,
                    l.`Locality_Name` AS `locality_name`, 
                    d.`District_Name` AS `district_name`, 
                    r.`Region_Name` AS `region_name`,
                    ($full_name) AS `name`,
                    ($weight) AS `weight`,
                    `UniquenessLevel` AS `uniqueness_level`
               FROM `Russianpost_Locality` AS l
                    LEFT JOIN `Russianpost_Region` AS r ON (l.`Russianpost_Region_ID` = r.`Russianpost_Region_ID`)
                    LEFT JOIN `Russianpost_District` AS d ON (l.`Russianpost_District_ID` = d.`Russianpost_District_ID`)
              WHERE (l.`Locality_Name` LIKE '$locality%'";

        if ($filtered_locality && $locality !== $filtered_locality) {
            $query .= " OR l.`Locality_Name` LIKE '$filtered_locality%'";
        }
        $query .= ')';

        if ($district) {
            $query .= " AND d.`District_Name` LIKE '$district%'";
        }
        if ($region) {
            $query .= " AND r.`Region_Name` LIKE '$region%'";
        }

        $query .= " ORDER BY `weight` DESC, `name` ASC" .
                  " LIMIT " . self::MAX_RESULTS;

        return $db->get_results($query, ARRAY_A) ?: array();
    }

}