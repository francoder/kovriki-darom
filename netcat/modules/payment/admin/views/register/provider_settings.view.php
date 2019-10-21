<?php

if (!class_exists('nc_core')) {
    die;
}

echo $ui->controls->site_select($site_id);

if ($params_saved) {
    echo $ui->alert->success(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_PARAMS_SAVED);
}

$nc_core = nc_core::get_object();
$current_params = array('action' => 'save_provider_settings') + (array)$nc_core->input->fetch_get();
$form = $ui->form('?' . http_build_query($current_params, null, '&'))
           ->class_name('nc-margin-vertical-medium');

$register_provider_id = $nc_core->get_settings('PaymentRegisterProviderID', 'payment', false, $site_id);
$register_provider = nc_db()->get_row("SELECT `Value`, `PaymentRegister_Name` AS Name FROM `Classificator_PaymentRegister` WHERE `PaymentRegister_ID` = '" . $register_provider_id . "'");

// Основные настройки
/** @var nc_payment_register_provider $register_provider */
$register_provider = new $register_provider->Value;
$settings = $register_provider::get_settings_description();
foreach ($settings as $key => $value) {
    $$key = $this->nc_core->get_settings($key, 'payment', false, $site_id);
}

foreach ($settings as $name => $caption) {
    $form->add_row($caption)->vertical()->string('settings[' . $name . ']', $$name);
}

echo $form;