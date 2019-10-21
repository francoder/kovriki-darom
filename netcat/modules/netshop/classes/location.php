<?php

class nc_netshop_location {

    /** @var array классы для работы с адресами по странам */
    protected $provider_classes = array(
        'nc_netshop_location_provider_russianpost',
        'nc_netshop_location_provider_country',
    );

    /** @var  nc_netshop_location_provider[] */
    protected $providers;

    /** @var array  */
    protected $char_replacements = array('Ё' => 'Е', 'ё' => 'е');

    /**
     *
     * @param nc_netshop $netshop
     */
    public function __construct(nc_netshop $netshop) {
        $providers_setting = $netshop->get_setting('LocationProviders');
        if ($providers_setting) {
            $this->provider_classes = preg_split('/\s*,\s*/', $providers_setting, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    /**
     * @return nc_netshop_location_provider[]
     */
    protected function get_providers() {
        if ($this->providers === null) {
            $this->providers = array();
            foreach ($this->provider_classes as $class) {
                $instance = new $class;
                if ($instance instanceof nc_netshop_location_provider) {
                    $this->providers[$class] = new $class;
                } else {
                    trigger_error("Wrong 'LocationProviders' entry '$class': must implement nc_netshop_location_provider", E_USER_WARNING);
                }
            }
        }

        return $this->providers;
    }

    /**
     * @param $location_string
     * @return nc_netshop_location_data_collection
     */
    public function find_locations($location_string) {
        if (!trim($location_string)) {
            return new nc_netshop_location_data_collection();
        }

        $char_replacements = $this->char_replacements;
        if (!nc_core::get_object()->NC_UNICODE) {
            $char_replacements = nc_core::get_object()->utf8->array_win2utf($char_replacements);
        }
        $location_string = strtr($location_string, $char_replacements);

        $result = new nc_netshop_location_data_collection();
        foreach ($this->get_providers() as $provider) {
            $result->add_items(
                $provider->find_locations($location_string)->each('set', 'provider', get_class($provider))
            );
        }
        return $result;
    }

    /**
     * @param string $input_selector
     * @return string
     */
    public function get_suggest_script($input_selector = 'input[name=f_City]') {
        $nc_core = nc_core::get_object();
        $input_selector_quoted = addcslashes($input_selector, "'");
        $invalid_location_string = json_safe_encode(NETCAT_MODULE_NETSHOP_LOCATION_IS_INVALID);
        $suffix_placeholder = json_safe_encode(NETCAT_MODULE_NETSHOP_LOCATION_SUFFIX_PLACEHOLDER);

        $script = file_get_contents(nc_module_folder('netshop') . '/js/location_selector.min.js');
        $result = <<<END
<script>
var NETCAT_PATH = '{$nc_core->SUB_FOLDER}{$nc_core->HTTP_ROOT_PATH}';
ncLang = window.ncLang || {};
ncLang.NetshopLocationIsInvalid = $invalid_location_string;
ncLang.NetshopLocationSuffixPlaceholder = $suffix_placeholder;
$script
nc_netshop_init_location_input('$input_selector_quoted');
</script>
END;
        return $result;
    }

}