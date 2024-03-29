<?php

class nc_netshop_item_collection extends nc_record_collection {

    static protected $package_site_cache = array();

    protected $index_field = '';
    protected $items_class = 'nc_netshop_item';
//    protected $index_property = '_ItemKey';

    /**
     * Создаёт коллекцию товаров из массива вида
     * items[component][item][Qty] = 1
     * @param array $items_array
     * @return nc_netshop_item_collection
     */
    static public function from_array(array $items_array) {
        $collection = new self();
        foreach ($items_array as $component_id => $tmp) {
            foreach ($tmp as $item_id => $item_data) {
                // добавим Class_ID, Message_ID в $item_data:
                $item_data['Class_ID'] = $component_id;
                $item_data['Message_ID'] = $item_id;
                $collection->add(new nc_netshop_item($item_data));
            }
        }
        return $collection;
    }

    /**
     * Сумма по полю (с учётом количества из поля Qty)
     */
    public function get_field_sum($field_name) {
        return array_sum($this->each('get_field_total', $field_name));
    }

    /**
     * @param int $component_id
     * @param int $item_id
     * @return mixed
     */
    public function get_item_by_id($component_id, $item_id) {
        $key = "$component_id:$item_id";
        if ($this->index_property == '_ItemKey') {
            return $this->offsetGet($key);
        }
        else {
            return $this->first('_ItemKey', $key);
        }
    }

    /**
     * @param int $component_id
     * @param int $item_id
     */
    public function remove_item_by_id($component_id, $item_id) {
        $key = "$component_id:$item_id";
        if ($this->index_property == '_ItemKey') {
            unset($this->items[$key]);
        }
        else {
            foreach ($this->items as $index => $item) {
                if ($item['_ItemKey'] == $key) {
                    unset($this->items[$index]);
                    break;
                }
            }
        }
    }

    /**
     * SHA1-хэш, идентифицирующий содержимое корзины
     * @return string
     */
    public function get_hash() {
        $result = array();
        foreach ($this->items as $item) {
            $result[] = "$item[Class_ID]:$item[Message_ID]:$item[Qty]";
        }
        sort($result);
        return sha1(join(";", $result));
    }

    /**
     * Возвращает массив с суммой габаритов товаров в упаковке (габаритов посылки)
     * в сантиметрах.
     * Размеры берутся из свойств товара PackageSize1, PackageSize2, PackageSize3
     * (если отсутствуют, то из настроек магазина «Размер упаковки товара по умолчанию»).
     *
     * @return int[]
     */
    public function get_package_size() {
        if (!$this->items) {
            $netshop = nc_netshop::get_instance();
            return array(
                $netshop->get_setting('DefaultPackageSize1'),
                $netshop->get_setting('DefaultPackageSize2'),
                $netshop->get_setting('DefaultPackageSize3'),
            );
        }

        reset($this->items);
        $site_id = $this->first()->get('Catalogue_ID');
        $netshop = nc_netshop::get_instance($site_id);
        $default1 = $netshop->get_setting('DefaultPackageSize1');
        $default2 = $netshop->get_setting('DefaultPackageSize2');
        $default3 = $netshop->get_setting('DefaultPackageSize3');

        $distinct_dimension_values = array();
        $dimensions = array();

        foreach ($this->items as $item) {
            $qty = ceil($item['Qty']);
            for ($i = 0; $i < $qty; $i++) {
                $item_dimensions = array(
                    ceil($item['PackageSize1']) ?: $default1,
                    ceil($item['PackageSize2']) ?: $default2,
                    ceil($item['PackageSize3']) ?: $default3,
                );

                $distinct_dimension_values[$item_dimensions[0]] = true;
                $distinct_dimension_values[$item_dimensions[1]] = true;
                $distinct_dimension_values[$item_dimensions[2]] = true;

                $dimensions[] = $item_dimensions;
            }
        }

        // экспериментально подобранный коэффициент для разбивки товаров на группы
        $permutation_base = max(count($dimensions), count($distinct_dimension_values));
        $k = max(1, ceil(log($permutation_base, 10)));
        $max_items_per_chunk = max(2, ceil(8 / $k));

        return $this->get_combined_package_size($dimensions, $max_items_per_chunk);
    }

    /**
     * @param array[] $dimensions
     * @param $max_items_per_chunk
     * @return array
     */
    protected function get_combined_package_size(array $dimensions, $max_items_per_chunk) {
        // При большом количестве товаров число возможных комбинаций габаритов становится астрономическим.
        // Делим товары на части и комбинируем их.
        $num_dimensions = count($dimensions);
        if ($num_dimensions > $max_items_per_chunk) {
            $middle = ceil($num_dimensions / 2);
            $left = array_slice($dimensions, 0, $middle);
            $right = array_slice($dimensions, $middle);
            $chunks_dimension = array(
                $this->get_combined_package_size($left, $max_items_per_chunk),
                $this->get_combined_package_size($right, $max_items_per_chunk)
            );
            return $this->get_combined_package_size($chunks_dimension, $max_items_per_chunk);
        }

        // за основу взят алгоритм с http://openovate.com/lbc.php, (c) Openovate Labs
        // 1. Find total volume
        $volume = 0;
        // 2. Find WHD ranges
        $width_range = array();
        $height_range = array();
        $depth_range = array();

        foreach ($dimensions as $size) {
            $volume += $size[0] * $size[1] * $size[2];
            $width_range[] = $size[0];
            $height_range[] = $size[1];
            $depth_range[] = $size[2];
        }

        // 3. Order the WHD ranges
        sort($width_range);
        sort($height_range);
        sort($depth_range);

        // 4. Figure out every combination with WHD
        $combination = function($list) {
            $combination = array();
            $total = pow(2, count($list));
            for ($i = 0; $i < $total; $i++) {
                $set = 0;
                // For each combination check if each bit is set
                for ($j = 0; $j < $total; $j++) {
                    // Is bit $j set in $i?
                    if (pow(2, $j) & $i) {
                        $set += $list[$j];
                    }
                }

                if ($set !== 0) {
                    $combination[$set] = $set;
                }
            }

            sort($combination);
            return $combination;
        };

        $width_combination = $combination($width_range);
        $height_combination = $combination($height_range);
        $depth_combination = $combination($depth_range);

        $stacks = array();
        foreach ($width_combination as $width) {
            foreach ($height_combination as $height) {
                foreach ($depth_combination as $depth) {
                    $v = $width * $height * $depth;
                    if ($v >= $volume) {
                        $max_dimension = max($width, $height, $depth);
                        $stacks[$max_dimension][$v] = array($width, $height, $depth);
                    }
                }
            }
        }
        
        
        // выбираем варианты с минимальным наибольшим габаритом
        ksort($stacks, SORT_NUMERIC);
        $min_dimension_stacks = $stacks[key($stacks)];

        // среди них выбираем вариант с наименьшим объёмом
        ksort($min_dimension_stacks, SORT_NUMERIC);
        return $min_dimension_stacks[key($min_dimension_stacks)];
    }

}