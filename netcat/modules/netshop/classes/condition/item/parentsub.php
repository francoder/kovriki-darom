<?php

class nc_netshop_condition_item_parentsub extends nc_netshop_condition {

    /**
     * Parameters:
     *   op
     *   value   -- ID of the subdivision
     */

    protected $op;
    protected $value;

    /** @var array [ sub => parent ] */
    protected $parent_cache = array();

    /**
     * @param nc_netshop_condition_context $context
     * @param null $current_item
     * @return bool
     */
    public function evaluate(nc_netshop_condition_context $context, $current_item = null) {
        if (!$current_item instanceof nc_netshop_item) {
            return false;
        }

        $condition_sub_id = $this->value;
        $item_sub_id = $current_item['Subdivision_ID'];
        $nc_core = nc_core::get_object();

        // Если оператор — 'eq' (равно), при совпадении возвращаем true
        // Если оператор — 'ne' (не равно), при совпадении возвращаем false
        // (Другие операторы для этого типа условий не предусмотрены.)
        $result_for_subdivision_match = $this->op === 'eq';

        do {
            if ($this->compare($condition_sub_id, 'eq', $item_sub_id)) {
                return $result_for_subdivision_match;
            }
            $item_sub_id = $nc_core->subdivision->get_by_id($item_sub_id, 'Parent_Sub_ID');
        } while ($item_sub_id);

        return !$result_for_subdivision_match;
    }

    /**
     * @param nc_netshop $netshop
     * @return string
     */
    public function get_full_description(nc_netshop $netshop) {
        return ($this->op == 'ne'
                    ? NETCAT_MODULE_NETSHOP_COND_ITEM_PARENTSUB_NE
                    : NETCAT_MODULE_NETSHOP_COND_ITEM_PARENTSUB) .
               ' ' . $this->get_short_description($netshop);
    }

    /**
     * Короткое описание (только значение, для повторяющихся условий)
     * @param nc_netshop $netshop
     * @return string
     */
    public function get_short_description(nc_netshop $netshop) {
        try {
           $subdivision = nc_core('subdivision')->get_by_id($this->value);
           $subdivision_link = "<a href='$subdivision[HiddenURL]' target='_blank'>$subdivision[Subdivision_Name]</a>";
           return sprintf(NETCAT_MODULE_NETSHOP_COND_QUOTED_VALUE, $subdivision_link) . ' ' .
                  NETCAT_MODULE_NETSHOP_COND_ITEM_PARENTSUB_DESCENDANTS;
        }
        catch (Exception $e) {
            return $this->get_formatted_error_description(NETCAT_MODULE_NETSHOP_COND_NONEXISTENT_SUB);
        }

    }

    /**
     * @param nc_backup_dumper $dumper
     * @return array
     */
    public function get_updated_parameters_on_import(nc_backup_dumper $dumper) {
        return array('op' => $this->op,
                     'value' => $dumper->get_dict('Subdivision_ID', $this->value));
    }

}