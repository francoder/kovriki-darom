<?php

class nc_services_metrika_admin_ui extends nc_services_admin_ui
{

    /**
     * @param string $current_action
     */
    public function __construct($current_action = "index", $counter_id, $tab = '')
    {
        parent::__construct('metrika', NETCAT_MODULE_SERVICES_METRIKA);

        $this->_action = $current_action;
        $this->activeTab = $tab;
        $this->counter_id = $counter_id;

        $this->treeSelectedNode = "services-metrika";
    }

    /**
     * @return string
     */
    public function to_json()
    {
        if ($this->locationHash == 'module.services.metrika') {
            if ($this->_action != "index") {
                $this->locationHash = "module.services.metrika." . $this->_action;
            }
        }
        if ($this->_action == 'stat') {
            $this->tabs = array(
              array(
                'id' => 'traffic',
                'caption' => NETCAT_MODULE_SERVICES_METRIKA_STAT_TRAFFIC,
                'location' => "module.services.metrika.stat.traffic(" . $this->counter_id.")",
                'group' => "admin",
              ),
              array(
                'id' => 'sources',
                'caption' => NETCAT_MODULE_SERVICES_METRIKA_STAT_SOURCES,
                'location' => "module.services.metrika.stat.sources(" . $this->counter_id.")",
                'group' => "admin",
              ),
              array(
                'id' => 'users',
                'caption' => NETCAT_MODULE_SERVICES_METRIKA_STAT_USERS,
                'location' => "module.services.metrika.stat.users(" . $this->counter_id.")",
                'group' => "admin",
              ),
              array(
                'id' => 'content',
                'caption' => NETCAT_MODULE_SERVICES_METRIKA_STAT_CONTENT,
                'location' => "module.services.metrika.stat.content(" . $this->counter_id.")",
                'group' => "admin",
              ),
              array(
                'id' => 'tech',
                'caption' => NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH,
                'location' => "module.services.metrika.stat.tech(" . $this->counter_id.")",
                'group' => "admin",
              ),
            );
        }

        return parent::to_json();
    }

}
