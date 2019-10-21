<?php

$NETCAT_FOLDER = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR;

require_once $NETCAT_FOLDER . 'vars.inc.php';
require_once $ADMIN_FOLDER . 'function.inc.php';

$nc_core = nc_core::get_object();
$db = $nc_core->db;

// Число заказов показывается только для сайтов, на которых используется тот же компонент
// заказов, что и на сайте, на адресе которого открыта админка

$site_id = $nc_core->catalogue->get_current('Catalogue_ID');
$netshop = nc_netshop::get_instance($site_id);
$order_table = $netshop->get_order_table_name();
$stat_link = $nc_core->SUB_FOLDER . $nc_core->HTTP_ROOT_PATH . 'modules/netshop/admin/?controller=statistics&action=index&site_id=' . $site_id;

$today = date('Y-m-d 00:00:00');
$yesterday = date('Y-m-d 00:00:00', strtotime('yesterday'));

$num_orders_today = $db->get_var("SELECT COUNT(*) FROM `$order_table` WHERE `Created` >= '$today'");
$num_orders_yesterday = $db->get_var("SELECT COUNT(*) FROM `$order_table` WHERE `Created` >= '$yesterday' AND `Created` < '$today'");

$num_new_orders = $db->get_var("SELECT COUNT(*) FROM `$order_table` WHERE `Status` = 0 OR `Status` IS NULL");

?>

<table class="nc-widget-grid nc-widget-link nc-text-center" onclick="return nc.ui.dashboard.fullscreen(null, '<?= $stat_link ?>')">
    <!-- <col width="50%" />
          <col width="50%" /> -->
    <tr>
        <td class="nc-bg-light" style="height:1%" colspan="2">
            <?= NETCAT_MODULE_NETSHOP ?>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="nc-widget-cell-horizontal-padding">
            <table class="nc--wide">
                <tr>
                    <td class="nc-text-center nc--wide">
                        <dl class="nc-info<?= ($num_orders_today > 999 ? '' : ' nc--large') ?>">
                            <dt><?= $num_orders_today ?></dt>
                        </dl>
                    </td>
                    <td class="nc-text-center">
                        <dl class="nc-info nc--mini nc--vertical">
                            <dt><i class="nc-icon nc--mod-netshop nc--white"></i></dt>
                            <dd><?= DASHBOARD_TODAY ?></dd>
                        </dl>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr class="nc-widget-grid-row-compact">
        <td class="nc-bg-dark">
            <dl class="nc-info nc--mini">
                <dt>
                <dt><?= $num_orders_yesterday ?></dt>
                <dd><?= DASHBOARD_YESTERDAY ?></dd>
            </dl>
        </td>
        <td class="nc-bg-darken">
            <dl class="nc-info nc--mini">
                <dt>
                <dt><?= $num_new_orders ?></dt>
                <dd><?= DASHBOARD_WAITING ?></dd>
            </dl>
        </td>
    </tr>
</table>