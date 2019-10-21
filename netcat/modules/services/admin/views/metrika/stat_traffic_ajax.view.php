<?php
if (!class_exists('nc_core')) {
    die;
}
?>
<?php if (!empty($result['data']) && is_array($result['data']) && count($result['data']) > 0): ?>
    <div class="nc-panel-content nc-bg-lighten">
        <div class="nc-padding-20">
            <div class='nc-chart' id='nc_traffic_chart'></div>
        </div>
    </div>
    <div class="nc-panel-content nc-bg-lighten">
        <div class="nc--clearfix"></div>
        <div class="nc--left nc-padding-20">
            <table class='nc-table nc--bordered nc--striped'>
                <tr>
                    <th></th>
                    <th><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_DATE ?></th>
                    <th><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_VISITS ?></th>
                    <th><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_VIEWS ?></th>
                    <th><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_VISITORS ?></th>
                </tr>
                <tr class='nc-text-right'>
                    <td colspan="2"><b><?php echo NETCAT_MODULE_SERVICES_METRIKA_STAT_TOTAL ?></b></td>
                    <td><?php echo $result['totals']['visits'] ?></td>
                    <td><?php echo $result['totals']['page_views'] ?></td>
                    <td><?php echo $result['totals']['visitors'] ?></td>
                </tr>
                <?php
                $i = 0;
                foreach ($result['data'] as $k => $item):
                    $i++;
                    ?>
                    <tr class='nc-text-right'>
                        <td><?php echo $i; ?></td>
                        <td class='nc-text-left'><?php echo $item['date_text'] ?></td>
                        <td><?php echo $item['visits'] ?></td>
                        <td><?php echo $item['page_views'] ?></td>
                        <td><?php echo $item['visitors'] ?></td>
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
    var chart_ticks_interval = <?= $chart_ticks_interval ?>;
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
            stat.visits.lines = {show: true};
            stat.visits.points = {show: true};
            stat.page_views.points = {show: true};
            stat.page_views.lines = {show: true};
            stat.visits.bars = {show: false};
            stat.page_views.bars = {show: false};
            stat.visits.color = 6;
            nc.chart(nc('#nc_traffic_chart'), [stat.visits, stat.page_views], {xaxis: {tickLength: 20, mode: "categories"}});

            // for xaxis
            if (chart_ticks_interval > 1) {
                $nc.each($nc('.flot-x-axis').find('.flot-tick-label'), function(i, axis) {
                    if (i % chart_ticks_interval !== 0) {
                        $nc(axis).hide();
                    }
                });
                last_tick = $nc('.flot-x-axis').find('.flot-tick-label:last');
                last_tick.css("right", "40px");
                last_tick.css("left", "auto");
            }

        } else {
            nc('#nc_traffic_chart').html('<div class="nc-padding-20"><?= NETCAT_MODULE_SERVICES_DATA_NOT_AVAILABLE ?></div>');
        }
    })();
</script>