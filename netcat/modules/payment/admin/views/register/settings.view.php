<?

if (!class_exists('nc_core')) {
    die;
}

echo $ui->controls->site_select($site_id);

$current_params = array('action' => 'save_settings') + (array)$nc_core->input->fetch_get();
$form = $ui->form('?' . http_build_query($current_params, null, '&'))
           ->class_name('nc-margin-vertical-medium');

$register_providers = $db->get_col(
    "SELECT `PaymentRegister_ID`, `PaymentRegister_Name` FROM `Classificator_PaymentRegister`",
    1,
    0
) ?: array();
$register_providers = array('0' => NETCAT_MODERATION_LISTS_CHOOSE) + $register_providers;

$form->add()->input('hidden', 'settings[PaymentRegisterChecked]', 0);
$form->add_row()
     ->checkbox('settings[PaymentRegisterChecked]', $register_checked, NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_CHECKED);

$form->add()->input('hidden', 'settings[PaymentRegisterEmailReceipt]', 0);
$form->add_row()
     ->checkbox('settings[PaymentRegisterEmailReceipt]', $send_email, NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_EMAIL_RECEIPT_TO_CUSTOMER);

$form->add_row(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_EMAIL_FOR_WARNINGS)
    ->vertical()
    ->string('settings[PaymentRegisterWarningsEmail]', $register_warning_email);

$form->add_row(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_COMPANY_NAME)
    ->vertical()
    ->string('settings[PaymentRegisterCompanyName]', $company_name);


$form->add_row(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_INN)
    ->vertical()
    ->string('settings[PaymentRegisterINN]', $register_inn);

$form->add_row(NETCAT_MODULE_PAYMENT_REGISTER_SN)->vertical()->select(
    'settings[PaymentRegisterSN]',
    nc_payment_register::get_tax_system_names(),
    $tax_system
);

$form->add_row(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_CURRENT)->vertical()->select('settings[PaymentRegisterProviderID]', $register_providers, $register_provider_id);
$href_params = array_merge($current_params, array('action' => 'provider_settings'));
$href = '?' . http_build_query($href_params, null, '&');
$form->add_row(
    nc_payment_register::get_provider_id($site_id)
        ? nc_ui_html::get('a')->href($href)
        ->text(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_GOTO_SETTINGS)
        : $ui->alert->info(NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_FILL_SETTINGS_TO_ACCESS)
);

echo $form;