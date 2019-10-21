<?php if (!class_exists('nc_core')) { die; } ?>

<?php

/** @var array $statistics */
/** @var array $logs */

?>

<!-- ----- STATISTICS ----- -->

<h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_STATISTICS; ?></h2>

<table class="nc-table nc--bordered nc--wide">
    <thead>
    <tr>
        <th class="nc--compact nc--nowrap"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_STATISTICS_ACTION; ?></th>
        <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_STATISTICS_ACTION_COUNT; ?></th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($statistics as $key => $val) { ?>

        <tr>
            <td class="nc--nowrap"><?= nc_netshop_exchange_helper::log_action_to_name($key); ?></td>
            <td><?= $val; ?></td>
        </tr>

    <?php } ?>

    </tbody>
</table>

<!-- ----- LOGS ----- -->

<h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_LOGS; ?></h2>

<table class="nc-table nc--bordered nc--wide">
    <thead>
    <tr>
        <th class='nc--compact nc--nowrap'><?= NETCAT_MODULE_NETSHOP_EXCHANGE_LOGS_DATE_TIME; ?></th>
        <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_MSG; ?></th>
        <th><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FILE; ?></th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($logs as $log) {
        $file_path = $scope_name = null;
        if (!empty($log['File_Path'])) {
            list($file_path, $scope_name) = explode('|', $log['File_Path']);
            $file_path = pathinfo($file_path, PATHINFO_BASENAME);
        } ?>

        <tr style="background-color: <?= nc_netshop_exchange_helper::log_type_to_color($log['Type']); ?>;">
            <td class="nc--nowrap"><?= $log['Created']; ?></td>
            <td><?= $log['Message']; ?></td>
            <td>

                <? if ($file_path) { ?>

                    <?= $file_path ?> (<?= $scope_name; ?>)

                <? } ?>

            </td>
        </tr>

    <?php } ?>

    </tbody>
</table>

<br>