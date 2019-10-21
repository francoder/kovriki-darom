<?php
if (!class_exists('nc_core')) {
    die;
}
?>
<?php echo $chart_init ?>
<?php echo $stat_init ?>

<script>
    nc_services_metrika_stat_settings.tab = '<?php echo $tab ?>';
    nc_services_metrika_stat_settings.group_by = 'day';
</script>

<div style='display:none; z-index:10000' id='nc_calendar_popup_from_d'></div>
<div style='display:none; z-index:10000' id='nc_calendar_popup_to_d'></div>
<div class="nc_admin_fieldset_head"><?= constant("NETCAT_MODULE_SERVICES_METRIKA_STAT_".strtoupper($tab)); ?></div>
<div id="nc_services_metrika_stat">
    <div class="nc-panel">
        <ul class="nc-nav-pills nc--right">
            <li><?= NETCAT_MODULE_SERVICES_METRIKA_GROUP_BY ?>:</li>
            <li class='nc--active'><a onclick="return nc_services_metrika_get_stat(this, {group_by: 'day'})" href="#"><?= NETCAT_MODULE_SERVICES_BY_DAY ?></a></li>
            <li><a onclick="return nc_services_metrika_get_stat(this, {group_by: 'week'})" href="#"><?= NETCAT_MODULE_SERVICES_BY_WEEK ?></a></li>
            <li><a onclick="return nc_services_metrika_get_stat(this, {group_by: 'month'})" href="#"><?= NETCAT_MODULE_SERVICES_BY_MONTH ?></a></li>
        </ul>
        <ul class="nc-tabs nc--small">
            <li><a onclick="nc_services_metrika_period_form(0);
                    return nc_services_metrika_get_stat(this, {period: 'today'})" href="#"><?= NETCAT_MODULE_SERVICES_TODAY ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0);
                    return nc_services_metrika_get_stat(this, {period: 'yesterday'})" href="#"><?= NETCAT_MODULE_SERVICES_YESTERDAY ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0);
                    return nc_services_metrika_get_stat(this, {period: '7days'})" href="#"><?= NETCAT_MODULE_SERVICES_WEEK ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0);
                    return nc_services_metrika_get_stat(this, {period: '30days'})" href="#"><?= NETCAT_MODULE_SERVICES_MONTH ?></a></li>
            <li><a id='nc_services_metrika_period_btn' onclick="return nc_services_metrika_period_form(1, this)" href="#"><?= NETCAT_MODULE_SERVICES_OVER_PERIOD ?></a></li>
        </ul>
    <?php if (!empty($tab) && $tab == 'sources'): ?>
        <ul class="nc-tabs nc--small">
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'summary'})" href="#"><?= NETCAT_MODULE_SERVICES_SOURCES_SUMMARY ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'sites'})" href="#"><?= NETCAT_MODULE_SERVICES_SOURCES_SITES ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'search_engines'})" href="#"><?= NETCAT_MODULE_SERVICES_SOURCES_SEARCH_ENGINES ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'phrases'})" href="#"><?= NETCAT_MODULE_SERVICES_SOURCES_PHRASES ?></a></li>
        </ul>
    <?php endif; ?>        
    <?php if (!empty($tab) && $tab == 'content'): ?>
        <ul class="nc-tabs nc--small">
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'popular'})" href="#"><?= NETCAT_MODULE_SERVICES_CONTENT_POPULAR ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'entrance'})" href="#"><?= NETCAT_MODULE_SERVICES_CONTENT_ENTRANCE ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'exit'})" href="#"><?= NETCAT_MODULE_SERVICES_CONTENT_EXIT ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'titles'})" href="#"><?= NETCAT_MODULE_SERVICES_CONTENT_TITLES ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'url_param'})" href="#"><?= NETCAT_MODULE_SERVICES_CONTENT_URL_PARAM ?></a></li>
        </ul>
    <?php endif; ?>        
    <?php if (!empty($tab) && $tab == 'tech'): ?>
        <ul class="nc-tabs nc--small">
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'browsers'})" href="#"><?= NETCAT_MODULE_SERVICES_TECH_BROWSERS ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'os'})" href="#"><?= NETCAT_MODULE_SERVICES_TECH_OS ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'display'})" href="#"><?= NETCAT_MODULE_SERVICES_TECH_DISPLAY ?></a></li>
            <li><a onclick="nc_services_metrika_period_form(0); return nc_services_metrika_get_stat(this, {type: 'mobile'})" href="#"><?= NETCAT_MODULE_SERVICES_TECH_MOBILE ?></a></li>
        </ul>
    <?php endif; ?>        

        <div id='nc_services_metrika_period_form' class='nc-form nc-padding-10 nc--hide'>
            <?= NETCAT_MODULE_SERVICES_FILTER_FROM ?>
            <input name="from_d" type="text" class='nc--mini' /> .
            <input name="from_m" type="text" class='nc--mini' /> .
            <input name="from_y" type="text" class='nc--small' />
            <span style='position:relative'>
                <button id='nc_calendar_popup_img_from_d' class='nc-btn nc--light' onclick="nc_calendar_popup('from_d', 'from_m', 'from_y')"><i class="nc-icon nc--calendar"></i></button>
            </span>
            &nbsp;&nbsp;&nbsp;
            <?= NETCAT_MODULE_SERVICES_FILTER_TO ?>
            <input name="to_d" type="text" class='nc--mini' /> .
            <input name="to_m" type="text" class='nc--mini' /> .
            <input name="to_y" type="text" class='nc--small' />
            <span style='position:relative'>
                <button id='nc_calendar_popup_img_to_d' class='nc-btn nc--light' onclick="nc_calendar_popup('to_d', 'to_m', 'to_y')"><i class="nc-icon nc--calendar"></i></button>
            </span>

            <button onclick="nc_services_metrika_show_period_stat()" class='nc-btn nc--blue'><?= NETCAT_MODULE_SERVICES_FILTER_SHOW ?></button>
        </div>
        <div class="nc-panel-content nc-bg-lighten"></div>
    </div>    
    <div class="nc-panel" id="stat_result">
    </div>
</div>

<script>
    (function() {
        $nc('#nc_services_metrika_stat ul.nc-tabs>li>a').first().click();
        $nc('#nc_services_metrika_stat ul.nc-tabs:eq(1)>li').first().addClass('nc--active');
    })();

    function nc_services_metrika_period_form(show, el) {
        if (show) {
            nc('#nc_services_metrika_period_form').slideDown();
        } else {
            nc('#nc_services_metrika_period_form').hide();
        }

        if (el) {
            nc(el).parents('ul').find('li').removeClass('nc--active');
            nc(el).parents('li').addClass('nc--active');

            var $panel_content = nc(el).parents('div.nc-panel').find('div.nc-panel-content');
            $panel_content.animate({opacity: .2}, 100);
        }

        return false;
    }
    function nc_services_metrika_show_period_stat() {
        var v = function(name) {
            return nc('#nc_services_metrika_period_form input[name=' + name + ']').val();
        }
        var d = function(name) {
            return v(name + '_y') + '-' + v(name + '_m') + '-' + v(name + '_d');
        }

        return nc_services_metrika_get_stat(nc('#nc_services_metrika_period_btn'), {period: d('from') + ':' + d('to')});
    }
</script>