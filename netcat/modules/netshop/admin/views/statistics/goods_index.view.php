<?php class_exists('nc_core') OR die ?>

<?=$chart_init ?>
<?=$stat_init ?>

<?= $ui->controls->site_select($catalogue_id) ?>

<script>
    nc_netshop_stat_settings.action = 'goods_by_period';
    nc_netshop_stat_settings.group_by = 'day';
</script>

<div id="nc_netshop_stat">

    <?=$period_stat ?>

    <?=$table ?>

</div>
