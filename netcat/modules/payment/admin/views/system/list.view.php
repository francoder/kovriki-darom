<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var array $payment_systems */
/** @var string $short_controller_name */

?>

<?= $ui->controls->site_select($site_id); ?>

<table class="nc-table nc--bordered nc--wide nc-margin-bottom-medium">
    <tr>
        <th class="nc--compact"></th>
        <th class="nc--wide"><?= NETCAT_MODULE_PAYMENT_PAYMENT_SYSTEM ?></th>
        <th><?= NETCAT_MODULE_PAYMENT_ADMIN_SETTINGS ?></th>
    </tr>
    <? foreach ($payment_systems as $system): ?>
        <?php
        $id = $system['id'];
        $is_enabled = $system['enabled'];
        $name = $system['name'];

        $edit_hash = "#module.payment.system.edit($site_id,$id)";
        $post_actions_params = array(
            'controller' => $short_controller_name,
            'site_id' => $site_id,
            'id' => $id,
        );
        ?>
        <tr>
            <td>
                <?= $ui->controls->toggle_button($is_enabled, $post_actions_params) ?>
            </td>
            <td>
                <? if ($is_enabled): ?>
                    <?= $ui->helper->hash_link($edit_hash, $name) ?>
                <? else: ?>
                    <?= $name ?>
                <? endif; ?>
            </td>
            <td class="nc-text-center">
                <?= $ui->helper->hash_link(
                    $edit_hash,
                    '<i class="nc-icon nc--settings' . ($is_enabled ? '' : ' nc--disabled') . '"></i>'
                ) ?>
            </td>
        </tr>
    <? endforeach; ?>
</table>
