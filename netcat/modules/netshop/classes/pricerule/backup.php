<?php

/**
 *
 */
class nc_netshop_pricerule_backup extends nc_netshop_backup {

    /**
     * @param string $type
     * @param int $id
     */
    public function export($type, $id) {
        if ($type != 'site') { return; }

        $price_rules = nc_db_table::make('Netshop_PriceRule')->where('Catalogue_ID', $id)->get_result();
        $this->dumper->export_data('Netshop_PriceRule', 'Netshop_PriceRule_ID', $price_rules);
    }

    /**
     * @param string $type
     * @param int $id
     */
    public function import($type, $id) {
        if ($type != 'site') { return; }

        $mapping_params = array(
            'UserGroup' => 'PermissionGroup_ID',
            'Condition' => array($this, 'update_condition_string'),
        );

        $this->dumper->import_data('Netshop_PriceRule', null, $mapping_params);
    }

}