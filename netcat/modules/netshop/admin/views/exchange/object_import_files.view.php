<?php if (!class_exists('nc_core')) { die; } ?>

<?= $this->include_view('modal', get_defined_vars()) ?>

<?php if ($uploaded) { ?>

    <div class="nc-alert nc--green"><i class="nc-icon-l nc--status-success"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_FILES_UPLOADED; ?>!</div>

<?php } ?>

<?

$files_count = count($files);
$acceptable_files_count = count($acceptable_files);

?>

<?php if ($acceptable_files_count || $object->get('remote_file_url')) { ?>

    <h3><?= NETCAT_MODULE_NETSHOP_EXCHANGE_RUNNING_EXCHANGE; ?></h3>

    <p>
        <a href="<?= nc_get_http_folder($ADMIN_PATH); ?>#module.netshop.exchange.wizard(report,<?= $catalogue_id; ?>,<?= $object->get('exchange_id'); ?>,2)"
           target="_top" class="nc-btn nc--mini nc--blue"
           onclick="nc_netshop_exchange_modal_show('modal-wait-exchange-finish');"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_RUN_EXCHANGE; ?></a>
    </p>

<?php } ?>

<h3><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FILES_IN_EXCHANGE_FOLDER; ?></h3>

<?php if ($acceptable_files_count || $files_count) { ?>

    <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_MAIN_FILES; ?><?= $acceptable_files_count ? " [{$acceptable_files_count}]" : ''; ?></h4>

    <?php if (count($acceptable_files)) { ?>
        <?php foreach ($acceptable_files as $file) { ?>

            <?= $file; ?><br>

        <?php } ?>
    <?php } else { ?>

        <div class="nc-alert nc--blue"><i class="nc-icon-l nc--status-info"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_MAIN_FILES . ' ' . NETCAT_MODULE_NETSHOP_EXCHANGE_NOT_FOUND; ?>.</div>

    <?php } ?>

    <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OTHER_FILES; ?><?= $files_count ? " [{$files_count}]" : ''; ?></h4>

    <?php if ($files_count) { ?>
        <?php $divide_count = 9; ?>
        <?php foreach ($files as $i => $file) { ?>

            <?= $file; ?><br>

            <?php if ($i == $divide_count) { ?>

                <button type="button" class="nc-btn nc--mini nc--blue"
                        onclick="$nc('#nc-netshop-exchange-spoiler').show();$nc(this).remove();"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SHOW_ALL; ?></button>
                <div id="nc-netshop-exchange-spoiler" style="display: none;">

            <?php } ?>

        <?php } ?>

        <?php if ($files_count >= $divide_count) { ?>

            </div>

        <?php } ?>
    <?php } else { ?>

        <div class="nc-alert nc--blue"><i class="nc-icon-l nc--status-info"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_OTHER_FILES . ' ' . NETCAT_MODULE_NETSHOP_EXCHANGE_NOT_FOUND; ?>.</div>

    <?php } ?>

<?php } else { ?>

    <div class="nc-alert nc--blue"><i class="nc-icon-l nc--status-info"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_FILES . ' ' . NETCAT_MODULE_NETSHOP_EXCHANGE_NOT_FOUND; ?>.</div>

<?php } ?>

<h3 class="nc-margin-top-medium"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_UPLOADING_FILES; ?></h3>

<div class="nc-field">
    <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_FILES; ?>:</span>
    <input type="file" name="files[]" multiple>
</div>
<div class="nc-field">
    <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OR_SET_FILES_URL; ?>:</span>
    <input type="text" name="file_url" value="<?= $object->get('remote_file_url'); ?>" size="64" class="ncf_value_text">
</div>

<label><input type="checkbox" name="delete_old" value="1"> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_REMOVE_OLD_FILES; ?></label>

<p class="nc-netshop-exchange-details"><?= $upload_info; ?></p>

<div class="nc-netshop-exchange-modal">
    <div class="nc-netshop-exchange-modal-item nc-netshop-exchange-modal-item--centered" id="modal-wait-exchange-finish">
        <div class="nc-netshop-exchange-modal-header">
            <h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_PLEASE_WAIT_EXCHANGE_FINISH; ?></h2>
        </div>
    </div>
</div>