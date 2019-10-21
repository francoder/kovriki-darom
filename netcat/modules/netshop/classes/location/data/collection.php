<?php

class nc_netshop_location_data_collection extends nc_record_collection {
    protected $items_class = 'nc_netshop_location_data';

    /**
     * @return array
     */
    public function to_compact_array() {
        $result = array();
        foreach ($this->items as $item) {
            /** @var nc_netshop_location_data $item */
            $row = array_filter($item->to_array(), 'strlen');
            unset(
                $row['provider'],
                $row['locality_id'],
                $row['weight'],
                $row['country_numeric_code'],
                $row['uniqueness_level']
            );
            $result[] = $row;
        }
        return $result;
    }
    
}