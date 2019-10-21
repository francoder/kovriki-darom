<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var nc_netshop_exchange_object $object */
/** @var nc_netshop_exchange_import $saved */
/** @var nc_netshop_exchange_import $cron_minutes */
/** @var nc_netshop_exchange_import $cron_hours */
/** @var nc_netshop_exchange_import $cron_days */

?>

<?php if ($saved) { ?>

    <div class="nc-alert nc--green"><i class="nc-icon-l nc--status-success"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SETTINGS_SAVED; ?></div>

<?php } ?>

<p>
    <a href="<?= nc_get_http_folder($ADMIN_PATH); ?>#module.netshop.exchange.wizard(start,<?= $catalogue_id; ?>,<?= $object->get('exchange_id'); ?>,1)"
       target="_top"
       class="nc-btn nc--mini nc--blue"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_RUN_WIZARD; ?></a>
</p>

<?php if ($object->get_mode() == nc_netshop_exchange_object::MODE_AUTOMATED) { ?>

    <label><input type="checkbox" name="exchange[automated_mode_enabled]"
        value="1" <?= $object->get('automated_mode_enabled') ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_AUTOMATED_MODE_ACTIVE; ?>
    </label>

<?php } ?>

<?php if ($object->get_mode() == nc_netshop_exchange_object::MODE_MANUAL) { ?>

    <label><input type="checkbox" name="exchange[run_interval_toggler]"
        value="1" <?= ($cron_minutes > 0 || $cron_hours > 0 || $cron_days > 0) > 0 ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_PERIODICAL_RUN; ?>
    </label>

    <div id="nc-netshop-exchange-exchange__run_interval" style="display: none;">
        <div class="nc-field">
            <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_PERIODICAL_RUN_FREQUENCY; ?> (*):</span>
            <?= NETCAT_MODULE_NETSHOP_EXCHANGE_PERIODICAL_RUN_MINUTES; ?>: <input type="number" name="exchange[run_interval_minutes]" value="<?= $cron_minutes ?: 0; ?>" min="0" step="1" class="nc-netshop-exchange-input--narrow">
            <?= NETCAT_MODULE_NETSHOP_EXCHANGE_PERIODICAL_RUN_HOURS; ?>: <input type="number" name="exchange[run_interval_hours]" value="<?= $cron_hours ?: 0; ?>" min="0" step="1" class="nc-netshop-exchange-input--narrow">
            <?= NETCAT_MODULE_NETSHOP_EXCHANGE_PERIODICAL_RUN_DAYS; ?>: <input type="number" name="exchange[run_interval_days]" value="<?= $cron_days ?: 0; ?>" min="0" step="1" class="nc-netshop-exchange-input--narrow">
        </div>
    </div>

    <br>

    <label><input type="checkbox" name="exchange[remote_file_url_toggler]" value="1" <?= $object->get('remote_file_url') ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_DOWNLOAD_REMOTE_FILE_BY_URL; ?></label>

    <div id="nc-netshop-exchange-exchange__remote_file_url" style="display: none;">
        <div class="nc-field">
            <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_REMOTE_FILE_URL; ?> (*):</span>
            <input type="text" name="exchange[remote_file_url]" value="<?= $object->get('remote_file_url'); ?>" size="64" class="ncf_value_text">
        </div>
    </div>

<?php } ?>

<br>

<label><input type="checkbox" name="exchange[email_toggler]" value="1" <?= $object->get('email') ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_SEND_REPORT; ?></label>

<div id="nc-netshop-exchange-exchange__email" style="display: none;">
    <div class="nc-field">
        <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_EMAIL; ?> (*):</span>
        <input type="email" name="exchange[email]" value="<?= $object->get('email'); ?>" size="64" class="ncf_value_text">
    </div>

    <div id="nc-netshop-exchange-exchange__report">
        <div class="nc-field">
            <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SEND_REPORT_WHEN; ?></span>
            <label><input type="radio" name="exchange[report]" value="<?= nc_netshop_exchange_object::EXCHANGE_REPORT_ALWAYS; ?>"
                <?= $object->get('report') == nc_netshop_exchange_object::EXCHANGE_REPORT_ALWAYS ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SEND_REPORT_ALWAYS; ?></label>

            <label><input type="radio" name="exchange[report]" value="<?= nc_netshop_exchange_object::EXCHANGE_REPORT_ON_ERROR; ?>"
                <?= $object->get('report') == nc_netshop_exchange_object::EXCHANGE_REPORT_ON_ERROR ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SEND_REPORT_ON_ERROR; ?></label>

            <label><input type="radio" name="exchange[report]" value="<?= nc_netshop_exchange_object::EXCHANGE_REPORT_NONE; ?>"
                <?= $object->get('report') == nc_netshop_exchange_object::EXCHANGE_REPORT_NONE ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SEND_REPORT_NEVER; ?></label>
        </div>
    </div>
</div>

<script>
(function() {
    var fields = [

        <?php if ($object->get_mode() == nc_netshop_exchange_object::MODE_MANUAL) { ?>

        'run_interval',
        'remote_file_url',

        <?php } ?>

        'email'
    ];

    function checkBlocks() {
        for (var i in fields) {
            var field = fields[i];

            var block = $nc(document).find('#nc-netshop-exchange-exchange__' + field);
            if ($nc(document).find('input[name="exchange[' + field + '_toggler]"]:checked').val()) {
                block.show();
            } else {
                block.hide();
            }
        }
    }

    $nc(function() {
        for (var i in fields) {
            var field = fields[i];

            $nc(document).on('change', 'input[name="exchange[' + field + '_toggler]"]', function() {
                checkBlocks();
            });
        }

        checkBlocks();
    });
})();
</script>