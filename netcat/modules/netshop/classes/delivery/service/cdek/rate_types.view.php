<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var array $all_rate_types */

?>

<div class="nc-netshop-delivery-cdek-rates-types">
    <?= NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_RATES ?>
    <?= $ui->alert->info(NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_SELECTED_RATES_NUMBER_WARNING) ?>

    <? foreach ($all_rate_types as $shipment_type => $rates): ?>
        <div class="nc-netshop-delivery-cdek-rates-type nc-netshop-delivery-cdek-rates-type-<?= $shipment_type ?>">
            <? foreach ($rates as $rate_id => $rate_data): ?>
                <div>
                    <label>
                        <input type="checkbox" value="<?= $rate_id ?>">
                        <?= $rate_data['name'] ?>
                        <? if ($rate_data['requires_login']): ?>
                            (<?= NETCAT_MODULE_NETSHOP_DELIVERY_CDEK_REQUIRES_LOGIN ?>)
                        <? endif; ?>
                    </label>
                </div>
            <? endforeach; ?>
        </div>
    <? endforeach; ?>
</div>
<script>
$nc(function() {
    var service_id_select = $nc('select[name="data[ShopDeliveryService_ID]"]'),
        rate_divs = $nc('.nc-netshop-delivery-cdek-rates-type'),
        checkboxes = rate_divs.find(':checkbox');

    // возвращает элемент для настройки службы доставки
    function get_settings_element(element_tag, setting_name) {
        return $nc(element_tag + '[name="delivery_service_settings[' + service_id_select.val() + '][' + setting_name + ']"]');
    }

    // показ только тарифов, соответствующих выбранному варианту отгрузки
    function change_shipment_type() {
        var selected_rates_selector =
            '.nc-netshop-delivery-cdek-rates-type-' +
            get_settings_element('select', 'shipment_type').val();
        rate_divs.hide();
        rate_divs.filter(selected_rates_selector).show();
    }

    // установить чекбоксы для выбранных тарифов
    function init_settings() {
        get_settings_element('select', 'shipment_type')
            .off('change.nc-netshop-delivery-cdek')
            .on('change.nc-netshop-delivery-cdek', change_shipment_type);
        change_shipment_type();

        try {
            var selected_rates = JSON.parse(get_settings_element('input', 'rate_types').val());
            checkboxes.each(function () {
                $nc(this).attr('checked', selected_rates.indexOf(parseInt(this.value, 10)) !== -1);
            })
        }
        catch (e) {}
    }
    service_id_select.change(init_settings);
    init_settings();

    // установить значение поля со списком выбранных тарифов при изменении выбора
    function change_rates() {
        var rate_ids = [];
        checkboxes.each(function() {
            if (this.checked) {
                rate_ids.push(parseInt(this.value, 10));
            }
        });
        get_settings_element('input', 'rate_types').val(JSON.stringify(rate_ids));
    }
    checkboxes.change(change_rates);
});
</script>