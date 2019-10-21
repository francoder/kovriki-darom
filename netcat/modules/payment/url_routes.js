urlDispatcher.addRoutes({
    'module.payment': NETCAT_PATH + 'modules/payment/admin/?controller=system&action=index&site_id=%1',
    'module.payment.system' : NETCAT_PATH + 'modules/payment/admin/?controller=system&action=index&site_id=%1',
    'module.payment.system.edit' : NETCAT_PATH + 'modules/payment/admin/?controller=system&action=edit&site_id=%1&id=%2',

    'module.payment.invoice' : NETCAT_PATH + 'modules/payment/admin/?controller=invoice&action=index&site_id=%1',
    'module.payment.invoice.view' : NETCAT_PATH + 'modules/payment/admin/?controller=invoice&action=view&id=%1',
    'module.payment.invoice.correction' : NETCAT_PATH + 'modules/payment/admin/?controller=invoice&action=correction&id=%1',

    'module.payment.receipt' : NETCAT_PATH + 'modules/payment/admin/?controller=receipt&action=index&site_id=%1',
    'module.payment.receipt.view' : NETCAT_PATH + 'modules/payment/admin/?controller=receipt&action=view&id=%1',
    'module.payment.receipt.resend' : NETCAT_PATH + 'modules/payment/admin/?controller=receipt&action=resend_confirmation&id=%1',

    'module.payment.register' : NETCAT_PATH + 'modules/payment/admin/?controller=register&action=settings&site_id=%1',
    'module.payment.register.provider' : NETCAT_PATH + 'modules/payment/admin/?controller=register&action=provider_settings&site_id=%1',
    'module.payment.register.log' : NETCAT_PATH + 'modules/payment/admin/?controller=register&action=log&site_id=%1',

    1: ''
});