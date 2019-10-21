<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var nc_netshop_exchange_object $object */
/** @var string $secret_name */
/** @var string $secret_key */
/** @var boolean $has_files */

?>

<h4><?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_HELP_STEP_1; ?></h4>

<?php

$table = $nc_core->ui->table()->wide()->striped()->hovered()->bordered()->small()->style("width:55%;margin-bottom:10px;");
$table->add_row(NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_EXTERNAL_URL, $nc_core->url->get_host_url() . '/netcat/modules/netshop/exchange/1c.php?id=' . $object->get('exchange_id'));
$table->add_row(NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_EXTERNAL_LOGIN, $secret_name);
$table->add_row(NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_EXTERNAL_PASSWORD, $secret_key);
echo $table;

?>

<h4><?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_HELP_STEP_2; ?></h4>

<p>
    <b><?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_FILE_STATUS; ?>:</b> <span style="color: <?= $has_files ? 'green' : 'red'; ?>;">
        <?= $has_files ? NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_FILE_STATUS_HAS : NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_FILE_STATUS_HAS_NOT; ?>
    </span>

</p>

<? if (!$has_files) { ?>

    <p><a href="javascript:location.reload();" class="nc-btn nc--mini nc--blue"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_FILE_STATUS_REFRESH; ?></a></p>

<? } ?>

<h4><?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_HELP_STEP_3; ?></h4>

<p class="nc-netshop-exchange-details">

    <? if ($has_files) { ?>

        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_HELP_STEP_3_DETAILS_CONTINUE; ?>

    <? } else { ?>

        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_HELP_STEP_3_DETAILS_WAIT; ?>

    <? } ?>

</p>

<!-- ----- SCRIPTS ----- -->

<script>
    function nc_netshop_exchange_wizard_form_submit() {

        <? if (!$has_files) { ?>

            alert('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_AUTOMATED_FILES_NOT_FOUND_PLEASE_WAIT; ?>');
            return false;

        <? } ?>

        return true;
    }
</script>