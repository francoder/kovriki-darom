<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var boolean $removed */
/** @var array $logs */
/** @var integer $exchange_id */
/** @var nc_netshop_exchange_log $log */

?>

<?php if ($removed) { ?>

    <div class="nc-alert nc--green"><i class="nc-icon-l nc--status-success"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_ALL_LOGS_HAVE_BEEN_DELETED; ?>!</div>

<?php } ?>

<?php if (!empty($logs)) { ?>

    <?php if (empty($exchange_id)) { ?>

        <table class="nc-table nc--bordered nc--wide">
            <thead>
            <tr>
                <th width="10%"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_LOG; ?></th>
                <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_DATE_START; ?></th>
                <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_DATE_END; ?></th>
                <th width="1%"><input type="checkbox" id="nc-netshop-exchange-chbox__toggle_all"></th>
            </tr>
            </thead>
            <tbody>

            <?php $counter = count($logs); ?>
            <?php foreach ($logs as $exchange_id => $data) { ?>

                <?php

                $view_hash = "#module.netshop.exchange.import(logs,{$object->get('exchange_id')},{$exchange_id})";

                ?>

                <tr>
                    <td>
                        <input type="hidden"">
                        <?= $ui->helper->hash_link($view_hash, NETCAT_MODULE_NETSHOP_EXCHANGE_LOG . ' &#8470;' . $counter--, 'nc-netshop-list-item-title'); ?>
                    </td>
                    <td><?= $data['Started']; ?></td>
                    <td><?= $data['Finished']; ?></td>
                    <td class="nc--compact nc-text-center">
                        <input type="checkbox" name="id[]" value="<?= $exchange_id; ?>" class="nc-netshop-exchange-chbox">
                    </td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

        <script>
        (function() {
            var chbs = $nc('.nc-netshop-exchange-chbox');
            var chbsCount = chbs.length;

            function getCheckedChbsCount() {
                return chbs.filter(':checked').length;
            }

            function updateState() {
                var checkedCount = getCheckedChbsCount();

                $nc('#nc-netshop-exchange-chbox__toggle_all').prop('checked', chbsCount == checkedCount ? 'checked' : '');
            }

            $nc('#nc-netshop-exchange-chbox__toggle_all').click(function() {
                var chboxToggleAll = $nc(this);

                $nc('input[name="id[]"]').each(function() {
                    $nc(this).prop('checked', chboxToggleAll.prop('checked') ? 'checked' : '');
                });

                updateState();
            });

            chbs.click(function() {
                var checkedCount = getCheckedChbsCount();

                $nc('#chb-toggle-all').prop('checked', checkedCount == chbs.length ? 'checked' : '');

                updateState();
            });
        })();
        </script>

    <?php } else { ?>

        <?= $log->build_report($exchange_id); ?>

    <?php } ?>

<?php } else { ?>

    <div class="nc-alert nc--blue"><i class="nc-icon-l nc--status-info"></i> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_LOGS_NOT_FOUND; ?>.</div>

<?php } ?>

<br>