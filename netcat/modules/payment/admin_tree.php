<?php

// Если скрипт вызывают напрямую а не через modules_json.php
if (empty($NETCAT_FOLDER)) {

    $NETCAT_FOLDER = realpath(dirname(__FILE__) . '/../../..') . DIRECTORY_SEPARATOR;
    require_once $NETCAT_FOLDER . 'vars.inc.php';
    require_once $ADMIN_FOLDER . 'function.inc.php';

    // Показываем дерево разработчика, если у пользователя есть на это права
    if (!$perm->isAccess(NC_PERM_MODULE, 0, 0, 0)) {
        exit(NETCAT_MODERATION_ERROR_NORIGHT);
    }
}

//--------------------------------------------------------------------------

if (empty($nc_core)) {
    $nc_core = nc_core();
}

//--------------------------------------------------------------------------

$module_node_id = 'module-' . $module['Module_ID'];

// Возвращаем путь (массив с ключами родительских элементов) к текущему разделу
if ($nc_core->input->fetch_get('action') == 'get_path') {
    $ret = array($module_node_id);
    echo nc_array_json($ret);
    exit;
}

//--------------------------------------------------------------------------

$node_children = array();

switch ($node_type) {

    case 'module':
        $node_children = array(
            // Платёжные системы
            array(
                'nodeId' => 'payment-system',
                'parentNodeId' => $module_node_id,
                'name' => NETCAT_MODULE_PAYMENT_ADMIN_PAYMENT_SYSTEMS,
                'href' => '#module.payment.system',
                'sprite' => 'settings',
                'hasChildren' => false,
                'expand' => false,
            ),

            // Кассы
            array(
                'nodeId' => 'payment-register',
                'parentNodeId' => $module_node_id,
                'name' => NETCAT_MODULE_PAYMENT_ADMIN_REGISTERS,
                'href' => '#module.payment.register',
                'sprite' => 'settings',
                'hasChildren' => false,
                'expand' => false,
            ),

            // Счета
            array(
                'nodeId' => 'payment-invoice',
                'parentNodeId' => $module_node_id,
                'name' => NETCAT_MODULE_PAYMENT_ADMIN_INVOICES,
                'href' => '#module.payment.invoice',
                'sprite' => 'bill',
                'hasChildren' => false,
                'expand' => false,
            ),

            // Чеки
            array(
                'nodeId' => 'payment-receipt',
                'parentNodeId' => $module_node_id,
                'name' => NETCAT_MODULE_PAYMENT_ADMIN_RECEIPTS,
                'href' => '#module.payment.receipt',
                'sprite' => 'mod-bills',
                'hasChildren' => false,
                'expand' => false,
            ),

        );
}

echo nc_array_json($node_children);