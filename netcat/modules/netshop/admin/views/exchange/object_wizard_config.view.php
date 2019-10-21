<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var nc_netshop_exchange_import $object */

?>

<?= $this->include_view('modal', get_defined_vars()) ?>

<label <?= $object->is_checked() ? 'style="display: none;"' : null; ?>><input type="checkbox" name="exchange[save]" value="1" <?= $object->is_checked() ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_SAVE_OBJECT; ?></label>

<div id="nc-netshop-exchange-exchange__name" style="display: none;">
    <div class="nc-field">
        <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_NAME; ?> (*):</span>
        <input type="text" name="exchange[name]" value="<?= $object->get('name'); ?>" size="64" class="ncf_value_text">
    </div>
</div>

<br>

<label><input type="checkbox" name="exchange[do_email]" value="1" <?= $object->get('email') ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_IS_SEND_REPORT; ?></label>

<div id="nc-netshop-exchange-exchange__email" style="display: none;">
    <div class="nc-field">
        <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_EMAIL; ?> (*):</span>
        <input type="email" name="exchange[email]" value="<?= $object->get('email'); ?>" size="64" class="ncf_value_text">
    </div>
</div>

<div class="nc-netshop-exchange-modal">
    <div class="nc-netshop-exchange-modal-item nc-netshop-exchange-modal-item--centered" id="modal-wait-exchange-finish">
        <div class="nc-netshop-exchange-modal-header">
            <h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_PLEASE_WAIT_EXCHANGE_FINISH; ?></h2>
        </div>
    </div>
</div>

<? if ($warnings) { ?>

    <h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_WARNINGS ?></h2>
    <p><?= NETCAT_MODULE_NETSHOP_EXCHANGE_LIMITS_WARNING ?></p>

    <? foreach ($warnings as $warning) { ?>

        <div class="nc-alert nc--yellow"><?= $warning; ?></div>

    <? } ?>

<? } ?>

<script>
    function nc_netshop_exchange_wizard_form_submit() {
        function validateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        if ($nc('input[name="exchange[save]"]:checked').val()) {
            if (!$nc('input[name="exchange[name]"]').val()) {
                alert('<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_NAME); ?>');
                return false;
            }
        }

        if ($nc('input[name="exchange[do_email]"]:checked').val()) {
            var value = $nc('input[name="exchange[email]"]').val();
            if (!value) {
                alert('<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_EMAIL); ?>');
                return false;
            } else {
                if (!validateEmail(value)) {
                    alert('<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_WRONG_FORMAT, NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_EMAIL); ?>');
                    return false;
                }
            }
        }

        nc_netshop_exchange_modal_show('modal-wait-exchange-finish');

        return true;
    }

    $nc(function() {
        function checkBlocks() {
            var block = $nc('#nc-netshop-exchange-exchange__name');
            if ($nc('input[name="exchange[save]"]:checked').val()) {
                block.show();
            } else {
                block.hide();
            }

            block = $nc('#nc-netshop-exchange-exchange__email');
            if ($nc('input[name="exchange[do_email]"]:checked').val()) {
                block.show();
            } else {
                block.hide();
            }
        }

        $nc('input[name="exchange[save]"]').change(function() {
            checkBlocks();
        });

        $nc('input[name="exchange[do_email]"]').change(function() {
            checkBlocks();
        });

        checkBlocks();
    });
</script>