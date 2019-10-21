<?php
if (!class_exists('nc_core')) {
    die;
}
?>
<?php if (!empty($result['data']) && is_array($result['data']) && count($result['data']) > 0): ?>
    <div class="nc-panel-content nc-bg-lighten">
        <div class="nc-padding-20">
            <div class='nc-chart' id='nc_tech_chart'></div>
        </div>
    </div>
    <div class="nc-panel-content nc-bg-lighten">
        <div class="nc--clearfix"></div>
        <div class="nc--left nc-padding-20">
            <table class='nc-table nc--bordered nc--striped'>
                <tr>
                    <th></th>
                    <th><?php echo constant("NETCAT_MODULE_SERVICES_METRIKA_STAT_TECH_".strtoupper(!empty($type) ? $type : "browsers")) ?></th>
                    <th><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_VISITS ?></th>
                    <th><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_VIEWS ?></th>
                </tr>
                <tr class='nc-text-right'>
                    <td colspan="2"><b><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_TOTAL ?></b></td>
                    <td><?php echo $result['totals']['visits'] ?></td>
                    <td><?php echo $result['totals']['page_views'] ?></td>
                </tr>
                <?php
                $i = 0;
                $from = ($type == 'display' ? 'data_group': 'data'); 
                foreach ($result[$from] as $k => $item):
                    $i++;
                    ?>
                    <tr class='nc-text-right'>
                        <td><?php echo $i; ?></td>
                        <td class='nc-text-left'><?php echo $item['name'] ?></td>
                        <td><?php echo is_array($item['visits']) ? array_sum($item['visits']) : $item['visits'] ?></td>
                        <td><?php echo is_array($item['page_views']) ? array_sum($item['page_views']) : $item['page_views'] ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
        <div class="nc--clearfix"></div>
    </div>
<?php elseif (isset($result['errors']) && count($result['errors']) > 0): ?>
    <?php foreach ($result['errors'] as $error): ?>
        <?php echo $ui->alert->error($error['text']); ?>
    <?php endforeach; ?>
<?php endif; ?>
<script type="text/javascript">
    var stat = <?= $chart_stat ?>;
    function showTooltip(x, y, contents, colour) {
        $('<div id="value">' + contents + '</div>').css({
            position: 'absolute',
            display: 'none',
            top: y,
            left: x,
            'border-style': 'solid',
            'border-width': '2px',
            'border-color': colour,
            'border-radius': '5px',
            'background-color': '#ffffff',
            color: '#262626',
            padding: '2px'
        }).appendTo("body").fadeIn(200);
    }
    (function() {

        nc.chart.set_defaults(<?= $chart_defaults ?>);
        nc.chart.set_defaults({"width": ($nc('#MainViewBody').outerWidth() - 85)});

        if (nc.key_exists('total', stat)) {
            nc.chart(nc('#nc_tech_chart'), stat.data, {series: {pie: { show: true} }, legend: {show: true}, grid: {hoverable: true, clickable: false}});
        } else {
            nc('#nc_tech_chart').html('<div class="nc-padding-20"><?= NETCAT_MODULE_SERVICES_DATA_NOT_AVAILABLE ?></div>');
        }
    })();
</script>