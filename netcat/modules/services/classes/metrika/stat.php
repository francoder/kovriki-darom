<?php

class nc_services_metrika_stat
{

    public $api;
    public $allowed_group_by = array('day', 'week', 'month');
    public $allowed_sources_types = array('summary', 'sites', 'search_engines', 'phrases');
    public $allowed_content_types = array('popular', 'entrance', 'exit', 'titles', 'url_param');
    public $allowed_tech_types = array('browsers', 'os', 'display', 'mobile');
    public $chart_stat = array();

    /**
     * Constructor
     * @param nc_services_metrika $metrika
     */
    public function __construct(nc_services_metrika $metrika)
    {
        $this->api = new nc_services_metrika_api();
        $this->api->set_token($metrika->auth_token);
        
        $tz = ini_get('date.timezone');
        if (empty($tz)) {
            date_default_timezone_set("Europe/Moscow");
        }
    }

    public function init_traffic_chart_data()
    {
        $this->chart_stat = array('ticks' => array(), 'visits' => array('data' => array(), 'label' => NETCAT_MODULE_SERVICES_METRIKA_STAT_VISITS),
          'page_views' => array('data' => array(), 'label' => NETCAT_MODULE_SERVICES_METRIKA_STAT_VIEWS),
          'visitors' => array());
    }

    public function init_sources_chart_data()
    {
        $this->chart_stat = array('data' => array());
    }

    public function init_users_chart_data()
    {
        $this->chart_stat = array('data' => array());
    }

    public function init_content_chart_data()
    {
        $this->chart_stat = array('data' => array());
    }

    public function init_tech_chart_data()
    {
        $this->chart_stat = array('data' => array(), 'data_group' => array());
    }

    public function filter_period($period = '')
    {
        $filter = array();
        switch ($period) {
            case "today":
                $filter['date2'] = date('Ymd', strtotime("today 12:00"));
                $filter['date1'] = date('Ymd', strtotime("today 12:00"));
                break;
            case "yesterday":
                $filter['date2'] = date('Ymd', strtotime("yesterday 12:00"));
                $filter['date1'] = date('Ymd', strtotime("yesterday 12:00"));
                break;
            case "7days":
                $filter['date2'] = date('Ymd');
                $filter['date1'] = date('Ymd', strtotime("-1 week"));
                break;
            case "30days":
                $filter['date2'] = date('Ymd');
                $filter['date1'] = date('Ymd', strtotime("-1 month"));
                break;
            default:
                $tmp_dates = explode(":", $period);
                $filter['date2'] = DateTime::createFromFormat('Y-m-d', $tmp_dates[1])->format('Ymd');
                $filter['date1'] = DateTime::createFromFormat('Y-m-d', $tmp_dates[0])->format('Ymd');
                break;
        }
        return $filter;
    }

    public function get_traffic($counter_id, $filter = null, $type = null)
    {
        $result = array();
        $this->api->reset_data();
        $this->api->set_method("stat/traffic/summary");
        $this->api->set_request_data("id", $counter_id);
        $this->apply_filter($filter);

        if ($this->api->send_request() === true) {
            $result = $this->api->result;
            foreach (array_reverse($result['data']) as $key => $item) {
                array_push($this->chart_stat['visits']['data'], array(DateTime::createFromFormat('Ymd', $item['date'])->format('d.m.Y'), $item['visits']));
                array_push($this->chart_stat['page_views']['data'], array(DateTime::createFromFormat('Ymd', $item['date'])->format('d.m.Y'), $item['page_views']));
                $result['data'][$key]['date_text'] = DateTime::createFromFormat('Ymd', $item['date'])->format('d.m.Y') .
                  (!empty($item['wday']) ? " " . constant("NETCAT_MODULE_SERVICES_WEEK_ABBR_" . $item['wday']) : "") .
                  (!empty($item['date2']) ? "-" . DateTime::createFromFormat('Ymd', $item['date2'])->format('d.m.Y') : "")
                ;
            }
        } else {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

    public function get_sources($counter_id, $filter = null, $type = null)
    {
        $result = array();
        $this->api->reset_data();
        $type = (!empty($type) && in_array($type, $this->allowed_sources_types)) ? $type : $this->allowed_sources_types[0];
        $this->api->set_method("stat/sources/" . $type);
        $this->api->set_request_data("id", $counter_id);
        $this->apply_filter($filter);
        if ($this->api->send_request() === true) {
            $result = $this->api->result;
            $result['totals']['visits'] = 0;
            $result['totals']['page_views'] = 0;
            foreach (array_reverse($result['data']) as $key => $item) {
                $result['totals']['visits'] += array_sum($item['visits']);
                $result['totals']['page_views'] += array_sum($item['page_views']);
                array_push($this->chart_stat['data'], array('data' => array_sum($item['visits']), 'label' => $item['name']));
            }
            $result['rows'] = count($result['data']);
        } else {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

    public function get_users($counter_id, $filter = null, $type = null)
    {
        $result = array();
        $this->api->reset_data();
        $this->api->set_method("stat/geo");
        $this->api->set_request_data("id", $counter_id);
        $this->apply_filter($filter);
        if ($this->api->send_request() === true) {
            $result = $this->api->result;
            $result['totals']['visits'] = 0;
            $result['totals']['page_views'] = 0;
            foreach (array_reverse($result['data']) as $key => $item) {
                $result['totals']['visits'] += array_sum($item['visits']);
                $result['totals']['page_views'] += array_sum($item['page_views']);
                array_push($this->chart_stat['data'], array('data' => array_sum($item['visits']), 'label' => $item['name']));
            }
            $result['rows'] = count($result['data']);
        } else {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

    public function get_content($counter_id, $filter = null, $type = null)
    {
        $result = array();
        $this->api->reset_data();
        $type = (!empty($type) && in_array($type, $this->allowed_content_types)) ? $type : $this->allowed_content_types[0];
        $this->api->set_method("stat/content/" . $type);
        $this->api->set_request_data("id", $counter_id);
        $this->apply_filter($filter);

        if ($this->api->send_request() === true) {
            $result = $this->api->result;

            $result['totals']['page_views'] = 0;

            if ($type === 'popular') {
                $result['totals']['exit'] = 0;
                $result['totals']['entrance'] = 0;
            } else if ($type == 'entrance' || $type == 'exit') {
                $result['totals']['visits'] = 0;
            }

            foreach (array_reverse($result['data']) as $key => $item) {
                if ($type === 'popular') {
                    $result['totals']['exit'] += array_sum($item['exit']);
                    $result['totals']['entrance'] += array_sum($item['entrance']);
                } else if ($type == 'entrance' || $type == 'exit') {
                    $result['totals']['visits'] += array_sum($item['visits']);
                }
                $result['totals']['page_views'] += array_sum($item['page_views']);
                array_push($this->chart_stat['data'], array('data' => array_sum($item[($type == 'exit' || $type == 'entrance') ? 'visits' : 'page_views']), 'label' => $item['name']));
            }
            $result['rows'] = count($result['data']);
        } else {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

    public function get_tech($counter_id, $filter = null, $type = null)
    {
        $result = array();
        $this->api->reset_data();
        $type = (!empty($type) && in_array($type, $this->allowed_tech_types)) ? $type : $this->allowed_tech_types[0];
        $this->api->set_method("stat/tech/" . $type);
        $this->api->set_request_data("id", $counter_id);
        $this->apply_filter($filter);
        if ($this->api->send_request() === true) {
            $result = $this->api->result;
            $result['totals']['visits'] = 0;
            $result['totals']['page_views'] = 0;
            if ($type == 'display') {
                $from = "data_group";
            } else {
                $from = "data";
            }
            foreach (array_reverse($result[$from]) as $key => $item) {
                $result['totals']['visits'] += is_array($item['visits']) ? array_sum($item['visits']) : $item['visits'];
                $result['totals']['page_views'] += is_array($item['page_views']) ? array_sum($item['page_views']) : $item['page_views'];
                array_push($this->chart_stat['data'], array('data' => is_array($item['visits']) ? array_sum($item['visits']) : $item['visits'], 'label' => $item['name']));
            }
            $result['rows'] = count($result[$from]);
            
        } else {
            if (!empty($this->api->error)) {
                $result['error'] = $this->api->error;
            }
        }
        return $result;
    }

    protected function apply_filter($filter = null)
    {
        if (!empty($filter) && is_array($filter)) {
            foreach ($filter as $key => $value) {
                if (!empty($value))
                    $this->api->set_request_data($key, $value);
            }
        }
    }

}
