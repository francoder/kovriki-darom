<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var string $step */
/** @var string $next_action */
/** @var boolean $has_files */
/** @var array $upload_error */
/** @var nc_netshop_exchange_import $object */
/** @var boolean $has_zip_extension */
/** @var nc_core $nc_core */
/** @var string $upload_info */
/** @var array $formats */
/** @var array $modes */
/** @var array $disabled_formats */

?>

<?php if (!empty($upload_error)) { ?>

    <div class="nc-alert nc--blue">
        <i class="nc-icon-l nc--status-info"></i>
        <?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_FILES_NOT_FOUND, $upload_error['acceptable_files_extensions']); ?>
    </div>

<? } ?>

<?php if (!$has_zip_extension) { ?>

    <div class="nc-alert nc--red">
        <i class="nc-icon-l nc--status-error"></i>
        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_ZIP_EXTENSION_NOT_FOUND; ?>
    </div>

<?php } ?>

<input type="hidden" name="object[type]" value="import">

<div id="nc-netshop-exchange-exchange__format">
    <div class="nc-field">
        <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_FORMAT; ?> (*):</span>

        <?php foreach ($formats as $key => $val) {
            $checked = ((isset($object) && $object['format'] == $key) || (!empty($upload_error['format']) && $upload_error['format'] == $key));
            $disabled = in_array($key, $disabled_formats); ?>

            <label>
                <input type="radio"
                       name="object[format]"
                       value="<?= $key; ?>"
                    <?= $checked ? ' checked ' : null; ?>
                    <?= $disabled ? ' disabled ' : null; ?>>
                <?= $val; ?>

                <? if ($disabled) { ?>

                    (<?= NETCAT_MODULE_NETSHOP_FUNCTION_IS_UNAVAILABLE; ?>)

                <? } ?>

            </label>
            <br>

        <?php } ?>

    </div>
</div>

<div id="nc-netshop-exchange-exchange__files" style="display: none;">

    <div id="nc-netshop-exchange-exchange__mode" style="display: none;">
        <div class="nc-field">
            <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_MODE; ?> (*):</span>

            <?php foreach ($modes as $key => $val) {
                $checked = isset($object) && $object['mode'] == $key; ?>

                <label>
                    <input type="radio"
                           name="object[mode]"
                           value="<?= $key; ?>"
                        <?= $checked ? ' checked ' : ''; ?>>
                    <?= $val; ?>

                </label>
                <br>

            <?php } ?>

        </div>
    </div>

    <div id="nc-netshop-exchange-exchange__mode-manual">
        <div class="nc-field">
            <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_FILE_OR_ARCHIVE; ?><?= !$has_files ? ' (*)' : null; ?>:</span>
            <input type="file" name="files[]" multiple>
        </div>
        <div class="nc-field">
            <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OR_SET_URL; ?><?= !$has_files ? ' (*)' : null; ?>:</span>
            <input type="text" name="file_url" value="<?= isset($object) ? $object->get('remote_file_url') : null; ?>" size="64" class="ncf_value_text">
        </div>

        <?php if ($has_files) { ?>

            <?= $nc_core->ui->alert(NETCAT_MODULE_NETSHOP_EXCHANGE_WIZARD_INFO_ALREADY_HAS_FILES)->blue(); ?>

        <?php } ?>

        <p class="nc-netshop-exchange-details"><?= $upload_info; ?></p>
    </div>

    <div id="nc-netshop-exchange-exchange__mode-automated" style="display: none;">
        <p class="nc-netshop-exchange-details"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_NOTIFICATION_FOR_AUTOMATED_MODE; ?></p>
    </div>

</div>

<script>
    function nc_netshop_exchange_wizard_form_submit() {
        if (!$nc('input[name="object[format]"]:checked').val()) {
            alert('<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_FORMAT); ?>');
            return false;
        }

        var objectFormatIsCml = $nc('input[name="object[format]"]:checked').val() === '<?= nc_netshop_exchange_import::FORMAT_CML; ?>';
        var objectModeIsManual = $nc('input[name="object[mode]"]:checked').val() === '<?= nc_netshop_exchange_object::MODE_MANUAL; ?>';

        if (!objectFormatIsCml || (objectFormatIsCml && objectModeIsManual)) {

            <?php if (!$has_files) { ?>

            if (!$nc('input[name="files[]"]').val() && !$nc(document).find('input[name="file_url"]').val()) {
                alert('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_ATTACH_FILES_OR_SET_URL; ?>');
                return false;
            }

            <?php } ?>

        }

        if (objectFormatIsCml && !$nc('input[name="object[mode]"]:checked').val()) {
            if (!$nc('input[name="object[mode]"]:checked').val()) {
                alert('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_CHOOSE_MODE; ?>');
                return false;
            }
        }

        return true;
    }

    $nc(function() {
        function checkBlocks() {
            if ($nc('input[name="object[format]"]:checked').val()) {
                $nc('#nc-netshop-exchange-exchange__files').show();
            }
            $blockExchangeMode = $nc('#nc-netshop-exchange-exchange__mode');
            $blockExchangeModeManual = $nc('#nc-netshop-exchange-exchange__mode-manual');
            $blockExchangeModeAutomated = $nc('#nc-netshop-exchange-exchange__mode-automated');
            if ($nc('input[name="object[format]"]:checked').val() === '<?= nc_netshop_exchange_import::FORMAT_CML; ?>') {
                $blockExchangeMode.show();
                switch ($nc('input[name="object[mode]"]:checked').val()) {
                    case '<?= nc_netshop_exchange_object::MODE_MANUAL; ?>': {
                        $blockExchangeModeManual.show();
                        $blockExchangeModeAutomated.hide();
                        break;
                    }
                    case '<?= nc_netshop_exchange_object::MODE_AUTOMATED; ?>': {
                        $blockExchangeModeManual.hide();
                        $blockExchangeModeAutomated.show();
                        break;
                    }
                    default: {
                        $blockExchangeModeManual.hide();
                        $blockExchangeModeAutomated.hide();
                    }
                }
            } else {
                $blockExchangeMode.hide();
                $blockExchangeModeManual.show();
                $blockExchangeModeAutomated.hide();
            }
        }

        $nc('input[name="object[format]"]').change(function() {
            checkBlocks();
        });
        $nc('input[name="object[mode]"]').change(function() {
            checkBlocks();
        });
        checkBlocks();
    });
</script>