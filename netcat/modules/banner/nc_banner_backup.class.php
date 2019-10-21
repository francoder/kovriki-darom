<?php

/**
 *
 */
class nc_banner_backup extends nc_backup_extension {

    /**
     * @param string $type
     * @param int $id
     */
    public function export($type, $id) {
        if ($type != 'site') { return; }

        $ids = array(
            "zone" => null,
            "script" => null,
            "script_exclusion" => null,
            "banner" => null,
            "campaign" => null);

        $infoblock_ids = $this->dumper->get_dict('Sub_Class_ID');
        foreach ($ids as $table => $nothingness) {
            $component_id = $this->get_component_id($table);
            $ids[$table] = nc_db_table::make("Message" . $component_id, "Message_ID")
                                ->where_in('Sub_Class_ID', $infoblock_ids)
                                ->get_list('Message_ID');
        }

        if ($ids['campaign']) {
            $campaign_banners = nc_db_table::make('Banner_CampaignBanners')
                                    ->where_in('Campaign_ID', $ids['campaign'])
                                    ->get_result();
            $this->dumper->export_data('Banner_CampaignBanner', null, $campaign_banners);

            $stats = nc_db_table::make('Banner_StatsCampaign')
                                    ->where_in('Campaign_ID', $ids['campaign'])
                                    ->get_result();
            $this->dumper->export_data('Banner_StatsCampaign', null, $stats);
        }

        if ($ids['zone']) {
            $campaign_zones = nc_db_table::make('Banner_CampaignZone')
                                    ->where_in('Zone_ID', $ids['zone'])
                                    ->get_result();
            $this->dumper->export_data('Banner_CampaignZone', null, $campaign_zones);

            $stats = nc_db_table::make('Banner_StatsZone')
                                    ->where_in('Zone_ID', $ids['zone'])
                                    ->get_result();
            $this->dumper->export_data('Banner_StatsZone', null, $stats);
        }

//        $log = nc_db_table::make('Banner_Log')
//                                ->where_in('Banner_ID', $ids['banner'])
//                                ->get_result();
//        $this->dumper->export_data('Banner_Log', 'Log_ID', $log);

        if ($ids['banner']) {
            $stats = nc_db_table::make('Banner_StatsBanner')
                                    ->where_in('Banner_ID', $ids['banner'])
                                    ->get_result();
            $this->dumper->export_data('Banner_StatsBanner', null, $stats);
        }
    }

    /**
     * @param string $type
     * @param int $id
     */
    public function import($type, $id) {
        if ($type != 'site') { return; }

        $callable = array($this, 'map_module_ids');
        $map_settings = array(
            "Banner_ID" => $callable,
            "Zone_ID" => $callable,
            "Campaign_ID" => $callable,
        );

        $this->dumper->import_data('Banner_CampaignBanner', null, $map_settings);
        $this->dumper->import_data('Banner_StatsBanner', null, $map_settings);
        $this->dumper->import_data('Banner_CampaignZone', null, $map_settings);
        $this->dumper->import_data('Banner_StatsCampaign', null, $map_settings);
        $this->dumper->import_data('Banner_StatsZone', null, $map_settings);
    }


    protected function get_component_id($type) {
        return intval(nc_core::get_object()->modules->get_vars("banner", strtoupper($type) . "_TABLE"));
    }

    public function map_module_ids($row, $field) {
        $component_type = null;

        if ($field == 'Banner_ID') { $component_type = 'banner'; }
        elseif ($field == 'Zone_ID') { $component_type = 'zone'; }
        elseif ($field == 'Campaign_ID') { $component_type = 'campaign'; }

        if ($component_type) {
            $component_id = $this->get_component_id($component_type);
            return $this->dumper->get_dict("Message{$component_id}.Message_ID", $row[$field]);
        }
        else {
            return $row[$field];
        }
    }
}