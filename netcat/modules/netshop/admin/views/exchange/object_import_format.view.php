<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var boolean $has_items */
/** @var array $items */

?>

<?php if (!$has_items) { ?>

    <div class="nc-alert nc--blue"><i class="nc-icon-l nc--status-info"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_NOT_CONFIGURED; ?>.</div>

    <?php return; ?>
<?php } ?>

<?php foreach ($items as $item) { ?>

    <h3><b><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SETTINGS_FOR_OBJECT; ?>:</b> "<?= $item['name']; ?>"</h3>

    <?php if (!empty($item['component'])) { ?>

        <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_COMPONENT; ?></h4>
        <?= $item['component']['id'] . '. ' . $item['component']['group'] . ': ' . $item['component']['name']; ?>
        <?= !empty($item['component']['keyword']) ? ' (' . $item['component']['keyword'] . ')' : null; ?>

    <?php } ?>

    <?php if (!empty($item['subdivision'])) { ?>

        <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SUBDIVISION; ?></h4>
        <?= $item['subdivision']['id'] . '. ' . $item['subdivision']['name'] . ' (' . $item['subdivision']['keyword'] . ')'; ?>

    <?php } ?>

    <?php if (!empty($item['sub_class'])) { ?>

        <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_INFOBLOCK; ?></h4>
        <?= $item['sub_class']['id'] . '. ' . $item['sub_class']['name'] . ' (' . $item['sub_class']['keyword'] . ')'; ?>

    <?php } ?>

<?php } ?>