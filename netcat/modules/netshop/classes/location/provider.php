<?php

abstract class nc_netshop_location_provider {

    /**
     * Ищет совпадения по адресу
     *
     * @param string $search_string
     * @return nc_netshop_location_data_collection
     */
    abstract public function find_locations($search_string);

}