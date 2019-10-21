<?php

if (!class_exists('nc_core')) {
    die;
}

/** @var nc_ui $ui */
/** @var int $site_id */
/** @var array $settings */
/** @var string $payment_system_name */

echo $ui->controls->site_select($site_id);

if (!empty($params_saved)) {
    echo $ui->alert->success(NETCAT_MODULE_PAYMENT_ADMIN_SETTINGS_SAVED);
}

$current_params = array('action' => 'save') + (array)nc_core::get_object()->input->fetch_get();
$form_action = '?' . http_build_query($current_params, null, '&');
$form = $ui->form($form_action)->vertical();
$form->add()->h3(sprintf(NETCAT_MODULE_PAYMENT_ADMIN_PAYMENT_SYSTEM_PARAMS, $payment_system_name));
$form->add_row();

foreach ($settings as $key => $value) {
    $row_html = nc_ui_html::get('div')
        ->style('display:inline-block; width:250px;')
        ->text($key);
    $form->add_row($row_html)->horizontal()->string('params[' . $key . ']', $value);
}

echo $form;
