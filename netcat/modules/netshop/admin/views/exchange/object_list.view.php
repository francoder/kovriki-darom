<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var array $objects */
/** @var array $not_finished_object */

?>

<?= $ui->controls->site_select($catalogue_id) ?>

<?php $common_hash = "#module.netshop.exchange"; ?>

<?php if (empty($objects)) { ?>

    <div class="nc-alert nc--blue">
        <i class="nc-icon-l nc--status-info"></i>
        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_HAS_NO_OBJECTS; ?>
    </div>

<?php } else { ?>

    <table class="nc-table nc--bordered nc--wide">
        <thead>
        <tr>
            <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_NAME; ?></th>
            <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_TYPE; ?></th>
            <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_FORMAT; ?></th>
            <th class='nc--compact'></th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($objects as $row): ?>
            <?php

            $id = $row['Exchange_ID'];
            $object_type = $row['Type'];
            $object_hash = "{$common_hash}.{$object_type}(settings,{$id})";
            $remove_action = $current_url . 'remove&id=' . $id;

            ?>
            <tr>
                <td>
                    <?= $ui->helper->hash_link($object_hash, $row['Name'], 'nc-netshop-list-item-title') ?>
                </td>
                <td><?= nc_netshop_exchange_helper::exchange_type_to_name($row['Type']); ?></td>
                <td><?= $row['Format']; ?></td>
                <td>
                    <?= $ui->controls->delete_button(NETCAT_MODULE_NETSHOP_SOURCES_DELETE_CONFIRM, array(
                        'controller' => $controller_name,
                        'id' => $id,
                    )); ?>
                </td>
            </tr>
        <? endforeach ?>

        </tbody>
    </table>

<?php } ?>

<?php if (!empty($not_finished_object)) { ?>
    <?php

    $object_id = $not_finished_object['exchange_id'];
    $object_type = $not_finished_object['type'];
    $object_format = $not_finished_object['format'];

    ?>
    <?= $this->include_view('modal', get_defined_vars()) ?>

    <div class="nc-netshop-exchange-modal">
        <div class="nc-netshop-exchange-modal-item nc-netshop-exchange-modal-item--narrow" id="modal-not-finished-object">
            <div class="nc-netshop-exchange-modal-header">
                <h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FOUND_NOT_FINISHED_OBJECT; ?></h2>
            </div>
            <div class="nc-netshop-exchange-modal-body">
                <p><b><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_NAME; ?>:</b> <?= $not_finished_object['name']; ?></p>
                <p><b><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_TYPE; ?>:</b> <?= nc_netshop_exchange_helper::exchange_type_to_name($object_type); ?></p>
                <p><b><?= NETCAT_MODULE_NETSHOP_EXCHANGE_OBJECT_FORMAT; ?>:</b> <?= nc_netshop_exchange_helper::exchange_format_to_name($object_format); ?></p>
            </div>
            <div class="nc-netshop-exchange-modal-footer">
                <?=
                    nc_ui_html::get('a')
                        ->class_name('nc-btn nc--mini nc--red')
                        ->text(NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_REMOVE)
                        ->post_vars(array(
                            'controller' => $controller_name,
                            'action' => 'remove',
                            'id' => $object_id,
                        ))
                    ->attr('data-confirm-message', NETCAT_MODULE_NETSHOP_SOURCES_DELETE_CONFIRM);
                ?>
                <?= $ui->helper->hash_link($common_hash . ".wizard(start,{$catalogue_id},{$object_id},1)", NETCAT_MODULE_NETSHOP_EXCHANGE_ACTION_CONTINUE, 'nc-btn nc--mini nc--blue') ?>
            </div>
        </div>
    </div>

    <script>
        $nc(function() {
            setTimeout(function() {
                nc_netshop_exchange_modal_show('modal-not-finished-object');
            }, 1000);
        });
    </script>

<?php } ?>