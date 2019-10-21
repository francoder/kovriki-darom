<?php

/**
 *
 */
class nc_services_metrika_admin_controller extends nc_services_admin_controller
{

    /** @var  nc_services_metrika_admin_ui */
    protected $ui_config;
    protected $ui_config_class = 'nc_services_metrika_admin_ui';
    protected $chart_defaults = array(
      'height' => 250,
    );
    protected $chart_ticks_num = 7;

    /**
     *
     */
    protected function init()
    {
        parent::init();
    }

    /**
     * @param array $param
     * @return string
     */
    protected function get_action_url(array $param = array())
    {
        $param_default = array(
            'controller' => 'metrika',
            'action' => 'stat',
            'counter_id' => $this->counter_id,
        );
        $path = array(
            'services',
            'admin',
        );
        return nc_get_action_url($path, array_merge($param_default, $param));
    }

    protected function view($view, $data = array())
    {
        $view = "metrika/" . $view;
        $view = parent::view($view, $data)
          ->with('link_prefix', nc_core('ADMIN_PATH') . '#module.services.metrika');
        return $view;
    }

    /**
     *
     */
    protected function before_action()
    {
        $this->counter_id = (int) $this->input->fetch_post_get('counter_id');
        $this->tab = $this->input->fetch_post_get('tab');
        $this->type = $this->input->fetch_post_get('type');

        $this->ui_config = new nc_services_metrika_admin_ui($this->current_action, $this->counter_id, $this->tab);
        if (empty($this->services->metrika->auth_token)) {
            echo parent::view('empty_error')->with('message', constant("NETCAT_MODULE_SERVICES_EMPTY_TOKEN"));
        }
    }

    /**
     * @return nc_ui_view
     */
    protected function action_index()
    {
        $add_link = "metrika.counter.add";
        $this->ui_config->add_create_button($add_link);

        $counters = $this->services->metrika->counter->get_list();

        if (count($counters) < 1 && $this->services->metrika->counter->api->error != "") {
            $view = parent::view('empty_error')->with('message', $this->services->metrika->counter->api->error);
        } else if (count($counters) == 0) {
            $message = constant("NETCAT_MODULE_SERVICES_METRIKA_NO_COUNTERS");
            $view = parent::view('empty_list')->with('message', $message);
        } else {
            $view = $this->view('counters_list')
              ->with('counters', $counters);
        }
        return $view;
    }

    protected function action_edit_counter()
    {
        $counter = array();
        if (!empty($this->counter_id)) {
            //get
            $result = $this->services->metrika->counter->get($this->counter_id);
            if (!empty($result['error'])) {
                echo parent::view('empty_error')->with('message', $result['error']);
            } else {
                $counter = $result;
            }
        }

        $view = $this->view('counter_edit')
          ->with('counter', $counter)
          ->with('counter_id', !empty($this->counter_id) ? $this->counter_id : 0)
        ;

        $this->ui_config->add_save_and_cancel_buttons();
        $this->ui_config->locationHash .=
          ($this->counter_id ? ".counter.edit({$this->counter_id})" : ".counter.add"
          );

        return $view;
    }

    protected function action_save_counter()
    {
        $data = (array) $this->input->fetch_post('data');
        $result = $this->services->metrika->counter->save($data);

        if (!empty($result['error'])) {
            echo parent::view('empty_error')->with('message', $result['error']);
        } else {
            //update code for first time
            if (empty($data['counter_id'])) {
                $this->services->metrika->counter->check_code($result['counter']['id']);
            }
            header("Location: " . nc_module_path('services') . 'admin/?controller=metrika&action=edit_counter&counter_id=' . $result['counter']['id']);
        }
    }

    protected function action_remove()
    {

        $id = (int) $this->input->fetch_get_post('counter_id');
        $result = $this->services->metrika->counter->remove($id);

        if (!empty($result['error'])) {
            echo parent::view('empty_error')->with('message', $result['error']);
        } else {
            parent::redirect_to_index_action();
        }
    }

    protected function action_update_status()
    {
        $id = (int) $this->input->fetch_get_post('counter_id');
        $this->services->metrika->counter->check_code($id);
        parent::redirect_to_index_action();
    }

    protected function action_stat()
    {

        $ajax = (int) $this->input->fetch_post_get('ajax');
        if ($ajax == 1) {

            // фильтрация
            $filter = $this->services->metrika->stat->filter_period($this->input->fetch_get_post('period'));
            $group_by = $this->input->fetch_get_post('group_by');
            $filter['group'] = in_array($group_by, $this->services->metrika->stat->allowed_group_by) ? $group_by : $this->services->metrika->stat->allowed_group_by[0];

            $init_chart_method = "init_".$this->tab."_chart_data";
            $this->services->metrika->stat->$init_chart_method();
            
            $method = "get_" . $this->tab;
            $result = $this->services->metrika->stat->$method($this->counter_id, $filter, $this->type);
            $this->services->metrika->stat->chart_stat['total'] = $result['rows'];

            $view = $this->view('stat_' . $this->tab . '_ajax')
              ->with('result', $result)
              ->with('type', (!empty($this->type) ? $this->type : "") )
              ->with('chart_stat', json_safe_encode($this->services->metrika->stat->chart_stat))
              ->with('chart_defaults', nc_array_json($this->chart_defaults))
              ->with('chart_ticks_interval', ($this->services->metrika->stat->chart_stat['total'] > $this->chart_ticks_num) ? intval($this->services->metrika->stat->chart_stat['total'] / $this->chart_ticks_num) : 0)

            ;
            echo $view;
            exit;
        }

        $data['controller_link'] = $this->get_action_url(array('tab' => $this->tab, 'ajax' => 1));

        $nc_core = nc_core::get_object();
        $view = $this->view('stat_common')
          ->with('counter_id', $this->counter_id)
          ->with('tab', $this->tab)
          ->with('stat_init', parent::view('metrika/stat_init', $data))
          ->with('chart_init', '<script src="' . nc_add_revision_to_url($nc_core->ADMIN_PATH . 'js/nc/nc.chart.min.js') . '"></script>')
        ;

        $this->ui_config->locationHash .= ".stat." . $this->tab . "({$this->counter_id})";

        return $view;
    }

}
