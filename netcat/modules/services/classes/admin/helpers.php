<?php

class nc_services_admin_helpers
{

    /**
     * @param string $parameters
     */
    static public function redirect_to_index_action($parameters = "")
    {
        ob_end_clean();
        header('Location: ?action=index' . ($parameters ? "&$parameters" : ""));
        die;
    }
    
    static public function get_regions_tree_html($items, $geo, $first = false) {
        $result = "<ul".($first === true ? " id='tree'": "").">";
        foreach ($items as $item) {
            $result .= "<li><label><input ".((is_array($geo) && count($geo) > 0 && in_array($item['RegionID'], $geo)) ? "checked": "")." type='checkbox' name='tmp[Geo][]' value='".$item['RegionID']."' />".$item['RegionName']."</label>";
            if (isset($item['childs'])) {
                $result .= self::get_regions_tree_html($item['childs'], $geo);
            }
            $result .= "</li>";
        }
        $result .= "</ul>";
        return $result;
    }

}
