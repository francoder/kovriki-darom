<?php
/* Module */
define("NETCAT_MODULE_PAYMENT_NAME", "���� ��������");
define("NETCAT_MODULE_PAYMENT_DESCRIPTION", "������ ��� ����� ��������");

/* Events description */
define("NETCAT_MODULE_PAYMENT_EVENT_ON_INIT", "������������� �������� �������");
define("NETCAT_MODULE_PAYMENT_EVENT_BEFORE_PAY_REQUEST", "���������� ������� �� ������");
define("NETCAT_MODULE_PAYMENT_EVENT_AFTER_PAY_REQUEST", "������ �� ������");
define("NETCAT_MODULE_PAYMENT_EVENT_ON_PAY_REQUEST_ERROR", "������ � ���������� ������� �� ������");
define("NETCAT_MODULE_PAYMENT_EVENT_BEFORE_PAY_CALLBACK", "���������� � ��������� callback-������ �������� �������");
define("NETCAT_MODULE_PAYMENT_EVENT_AFTER_PAY_CALLBACK", "��������� callback-������ �������� �������");
define("NETCAT_MODULE_PAYMENT_EVENT_ON_PAY_CALLBACK_ERROR", "������ � ���������� ��� callback-������");
define("NETCAT_MODULE_PAYMENT_EVENT_ON_PAY_SUCCESS", "����� ������� �������");
define("NETCAT_MODULE_PAYMENT_EVENT_ON_PAY_FAILURE", "������ �� ������������");
define("NETCAT_MODULE_PAYMENT_EVENT_ON_PAY_REJECTED", "����� �������");

define("NETCAT_MODULE_PAYMENT_ADMIN_PAYMENT_SYSTEMS", "�������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_PAYMENT_SYSTEM_PARAMS", "��������� �������� ������� &laquo;%s&raquo;");

/* Order description string */
define("NETCAT_MODULE_PAYMENT_PAYMENT_DESCRIPTION", "������ ������ �%s");
define("NETCAT_MODULE_PAYMENT_PAYMENT_CHARGE", "�������� �� ������������ �������� �������");

/* Error messages */
define("NETCAT_MODULE_PAYMENT_REQUEST_ERROR", "���� �������� �� ��������. �������������� ������� ����� ������ ������� <a href='https://netcat.ru/developers/docs/modules/module-payment/' target='_blank'>� ������������</a>.");
define("NETCAT_MODULE_PAYMENT_ORDER_ID_IS_NOT_UNIQUE", "����� ������ �� ����������");
define("NETCAT_MODULE_PAYMENT_ORDER_ID_IS_NULL", "�� ���������� �������� 'OrderId'");
define("NETCAT_MODULE_PAYMENT_INCORRECT_PAYMENT_AMOUNT", "����� ������� �� ������� ��� ������ �����������");
define("NETCAT_MODULE_PAYMENT_INCORRECT_PAYMENT_CURRENCY", "�������� ������� �� ��������� ������� � ������ �%s�");
define("NETCAT_MODULE_PAYMENT_CANNOT_LOAD_INVOICE_ON_CALLBACK", "�������� ������� ������� ������������ ������������� �������");
define("NETCAT_MODULE_PAYMENT_SETTING_MISSING_VALUE", "� ���������� �������� ������� �� ������ �������� �%s�");

/* admin */
define("NETCAT_MODULE_PAYMENT_PAYMENT_SYSTEM", "�������� �������");

define("NETCAT_MODULE_PAYMENT_ADMIN_SETTINGS", "���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_SAVE", "���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_SETTINGS_SAVED", "��������� ���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_TOTALS", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_STATUS", "������");
define("NETCAT_MODULE_PAYMENT_ADMIN_COPY_TO_CLIPBOARD", "�����������");
define("NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_BACK", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_TO_SYSTEM_LIST", "� ������ �������� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_CREATED", "������");

define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICES", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE", "����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SEARCH_PLACEHOLDER", "����� �� ������ �����, ������ ������, �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SEARCH_BUTTON", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_SOURCE", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_AMOUNT", "����� � ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_AMOUNT_SHORT", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CUSTOMER", "������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_EMPTY_LIST", "�� ��������� ����� ��� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_NO_MATCH", "����� � ���������� ����������� �� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_LAST_RESPONSE", "��������� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_PAYMENT_LINK", "������ ��� �������� � ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_BALANCE", "� �������&nbsp;/ ��������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_MANUAL_REFUND", "��� ����������� �������� ����� � ����������� ������� ������� ������ �� ������ ��������� ������� ������� � �������� �������, ����� ���� ���������� � ����� ����� ������ ��������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CHANGE", "�������� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ALREADY_PAID", "��������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ON_STATUS_CHANGE_SELL_RECEIPT", "��� ��������� ������� �� �������� � ��� ����� ��������������� ��� � ��������� ������� ������� �� ����� <strong>%s</strong>");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ON_STATUS_CHANGE_SELL_REFUND_RECEIPT", "��� ��������� ������� �� �������� � ��� ����� ��������������� ��� � ��������� ������� �������� ������� �� ����� <strong>%s</strong>");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ON_STATUS_CHANGE_SELL_SELL_REFUND_RECEIPTS", "��� ��������� ������� �� �������� � ��� ����� ���������������� ��� � ��������� ������� ������� �� ����� <strong>%s</strong> � ��� � ��������� ������� �������� ������� �� ����� <strong>%s</strong>");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_REFUND_PAYMENT_SYSTEM_NO_RECEIPT_WARNING", "��� ��������� ������� �� �������� �������� ��� �������� ������� <strong>�� ����� ������</strong> �������������, ��� ��� ������������ �������� ������� �� ������������ ������ �����������. ��� �������� ����� �������� ��������� ����������� � ��������� �������.");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_INCORRECT_PHONE", "������������ �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_INCORRECT_EMAIL", "������������ �����");

define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_BUTTON", "������� � �������������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUB_HEADER", "�������� ����������������� ����� (����� %s)");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_CREATE_BUTTON", "������� ���������������� ����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_HEADER", "��������� ������������ ������� �� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_FULL_REFUND_BUTTON", "������ ������� ���� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_PRIMARY_INVOICE", "�������������� ����:");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_CANCELLED_INVOICES", "����� �������� ����������������� ����� ��������� ������������ ����� ����� ���������� � ������ �������:");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_AFTER", "����� ����� ���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUM", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_FULL_REFUND_CHECKBOX", "������ �������?");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_NO_CHANGES", "���������������� ���� �� ��� ������, ��� ��� ����� ������� �� ���������� �� ������� � ������������ ������ ��� ����� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_ADD_ITEMS", "���������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_ADD_ITEM", "��������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_REMOVE_ITEM", "�������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUMMARY_AMOUNT", "����� ������������ ����������������� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUMMARY_EXTRA", "������� �� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_CORRECTION_SUMMARY_REFUND", "������� �������");

define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEMS", "������� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_BALANCE", "������ ������� �� ������ ");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_NAME", "������������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_PRICE", "����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_QTY", "����������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_TOTALS", "���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_RATE", "������ ���");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_AMOUNT", "����� ���");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_NONE", "���");

define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_ERROR", "������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_NEW", "�����, �� ����������� � �������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_NEW_SHORT", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SENT", "��������� � �������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SENT_SHORT", "���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_SUCCESS", "�������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CALLBACK_ERROR", "������ ��� ��������� callback-������ �� �������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CALLBACK_WRONG_SUM", "������ � ����� � callback-������ �� �������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_WAITING", "�������� ������ �� �������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_REJECTED", "������� �������� ��������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_STATUS_CANCELLED", "������");
define("NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ALL_FOR_ORDER", "��� ����� �� ������");

define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPTS", "����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT", "���");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_EMPTY_LIST", "�� ��������� ����� �� ���� ������� �� ������ ����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_ITEMS", "������� ����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_EVENTS", "�������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_RESEND_BUTTON", "�������� ��������� � ���");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_RESEND_CONFIRMATION", "��� %s ����� �������� ��������� � ��� (����� %s).");

define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_ID", "ID");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SHIFT_NUMBER", "����� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_SERIAL_NUMBER", "���������� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTRATION_TIME", "����� �����������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_AMOUNT", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_BALANCE", "������ �� ������������������ �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTER", "�������� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_FISCAL_STORAGE_NUMBER", "����� ����������� ����������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_REGISTER_NUMBER", "��������������� ����� ���");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_FISCAL_DOCUMENT_NUMBER", "���������� ����� ���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_FISCAL_DOCUMENT_ATTRIBUTE", "���������� ������� ���������");

define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_NEW", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_PENDING", "���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_REGISTERED", "���������������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_FAILED", "������");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_CONNECTION_ERROR", "��� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_RECEIPT_STATUS_CANCELLED", "������");

define("NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION", "���");
define("NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION_SELL", "������");
define("NETCAT_MODULE_PAYMENT_RECEIPT_OPERATION_SELL_REFUND", "������� �������");

define("NETCAT_MODULE_PAYMENT_ADMIN_LOG", "������ �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_LOG_TIME", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_LOG_EVENT", "�������");
define("NETCAT_MODULE_PAYMENT_ADMIN_LOG_RECEIPT_STATUS", "������ ����");
define("NETCAT_MODULE_PAYMENT_ADMIN_LOG_SHOW_DETAILS", "��������� ��������");

define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_CREATED", "������ ��� �%s");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_SENT", "��� �%s ��������� �� ���");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_NO_REPLY", "��� ������ �� ��� (��� �%s)");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_GOT_REPORT", "������� ����� �� ���� �%s");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_ERROR", "������ ��������� ���� �%s");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_RECEIPT_CANCELLED", "��������� ���� �%s ��������");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_Z_REPORT", "�������� Z-������");
define("NETCAT_MODULE_PAYMENT_REGISTER_LOG_OPEN_SHIFT", "�������� �����");

define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTERS", "�����");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_PARAMS_SAVED", "��������� ������� ���������");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS", "��������� ���������� � �������� &laquo;%s&raquo;");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_COMPANY_NAME", "������������ ��������");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_INN", "���");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_EMAIL_RECEIPT_TO_CUSTOMER", "���������� ������� ����� ���� �� ����������� �����");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_SETTINGS_EMAIL_FOR_WARNINGS", "����� ����������� ����� ��� ����������� � ��������� � ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_COMMON_SETTINGS", "����� ��������� ���������� � �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_CHECKED", "���������� ����");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_CURRENT", "������������ �������� ������");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_GOTO_SETTINGS", "� ���������� ��������� �������");
define("NETCAT_MODULE_PAYMENT_ADMIN_REGISTER_FILL_SETTINGS_TO_ACCESS", "��������� ����� ���������, ����� ������� � ���������� ��������� �������.");

define("NETCAT_MODULE_PAYMENT_REGISTER_SN", "������� ���������������");
define("NETCAT_MODULE_PAYMENT_REGISTER_SN_OSN", "������������");
define("NETCAT_MODULE_PAYMENT_REGISTER_SN_USN_INCOME", "���������� (������)");
define("NETCAT_MODULE_PAYMENT_REGISTER_SN_USN_INCOME_OUTCOME", "���������� (������ ����� �������)");
define("NETCAT_MODULE_PAYMENT_REGISTER_SN_ENVD", "������ ����� �� �������� �����");
define("NETCAT_MODULE_PAYMENT_REGISTER_SN_ESN", "������ �������������������� �����");
define("NETCAT_MODULE_PAYMENT_REGISTER_SN_PATENT", "���������");

define("NETCAT_MODULE_PAYMENT_REGISTER_MAIL_ADMIN_WARNING_SUBJECT", "������ ��� ������� ����������� ��������� ����");
define("NETCAT_MODULE_PAYMENT_REGISTER_MAIL_ADMIN_WARNING_BODY_INTRO", "��������� ������ ��� ������� ����������� ��������� ����:");
define("NETCAT_MODULE_PAYMENT_REGISTER_MAIL_ADMIN_WARNING_BODY_LINK", "��������� �������� � ���� �������� � ������ ����������:");
define("NETCAT_MODULE_PAYMENT_REGISTER_MAIL_RECEIPT_SUBJECT", "����������� ����� ����");

define("NETCAT_MODULE_PAYMENT_REGISTER_ATOL_LOGIN", "�����");
define("NETCAT_MODULE_PAYMENT_REGISTER_ATOL_PASSWORD", "������");
define("NETCAT_MODULE_PAYMENT_REGISTER_ATOL_GROUP", "������ ����");
define("NETCAT_MODULE_PAYMENT_REGISTER_ATOL_PAYMENT_ADDRESS", "����� ����� �������� (�������� ��� �����)");

define("NETCAT_MODULE_PAYMENT_REGISTER_ECOMKASSA_URL", "URL �������");
define("NETCAT_MODULE_PAYMENT_REGISTER_ECOMKASSA_LOGIN", "�����");
define("NETCAT_MODULE_PAYMENT_REGISTER_ECOMKASSA_PASSWORD", "������");
define("NETCAT_MODULE_PAYMENT_REGISTER_ECOMKASSA_GROUP", "ID ��������");
define("NETCAT_MODULE_PAYMENT_REGISTER_ECOMKASSA_PAYMENT_ADDRESS", "����� ����� �������� (�������� ��� �����)");

define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_HEADER", "�������� ����������� ��� ��������� ����");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_URL", "����� KKM ������� (� ��������� ��������� � �����, ��������: &quot;http://1.2.3.4:5893&quot;)");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_LOGIN", "�����");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_PWD", "������");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_NUMDEVICE", "����� ����������. ���� �� ���������, �� ������ �� ������������� �� �������");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_KKMINN", "��� ��� ��� ������. ���� �� ���������, �� ��� ������ ������ �� NumDevice; ���� NumDevice = 0, � InnKkm ���������, �� ��� ������ ������ �� InnKkm");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_KKMNUM", "��������� ����� ��� ��� ������. ���� �� ���������, �� ��� ������ ������ �� NumDevice");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_TIMEOUT", "����� (� ��������) �������� ���������� ������� (�� ��������� 30 �)");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_CASHIER", "��� ������� (��� ��� 1021)");
define("NETCAT_MODULE_PAYMENT_REGISTER_KKMSRV_SHIFT_START_TIME", "����� ������ Z-������ � �������� ����� ����� (� ������� ��:��)");

define("NETCAT_MODULE_PAYMENT_REGISTER_CLOUDKASSIR_PUBLIC_ID", "������������� �����");
define("NETCAT_MODULE_PAYMENT_REGISTER_CLOUDKASSIR_SECRET_KEY", "��������� ����");

define("NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_SHOP_ID", "������������� ��������");
define("NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_SHOP_SECRET", "��������� ���� ��������");
define("NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_QUEUE_ID", "������������� �������");
define("NETCAT_MODULE_PAYMENT_REGISTER_KOMTETKASSA_PRINT_CHECK", "������ ��������� ���� (��/���)");

/* Payment common messages */
define("NETCAT_MODULE_PAYMENT_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ ������� �����");
define("NETCAT_MODULE_PAYMENT_ERROR_INVOICE_NOT_FOUND", "����� �� ������ � NetCat");
define("NETCAT_MODULE_PAYMENT_ERROR_INVALID_SUM", "�������� ����� �������");
define("NETCAT_MODULE_PAYMENT_ERROR_ALREADY_PAID", "���� ��� �������");
define("NETCAT_MODULE_PAYMENT_ERROR_AUTH_REQUIRED", "���������� ����� �� ����, ����� �������� ���� ����");
define("NETCAT_MODULE_PAYMENT_ERROR_WRONG_USER", "������ ���� ��������� ������� ������������");

define("NETCAT_MODULE_PAYMENT_SETTINGS_LINK", "������� ��������� �������� �������");

/* Payment form texts */
define("NETCAT_MODULE_PAYMENT_FORM_PAY", "��������");
define("NETCAT_MODULE_PAYMENT_FORM_PAY_SELECT", "�������� ������ ������");

/* Assist */
define("NETCAT_MODULE_PAYMENT_ASSIST_ERROR_CHECKVALUE_IS_NOT_VALID", "������������ �������� 'CheckValue'");
define("NETCAT_MODULE_PAYMENT_ASSIST_ERROR_ASSIST_SHOP_ID", "�������� 'AssistShopId' ������ ���� ������");
define("NETCAT_MODULE_PAYMENT_ASSIST_ERROR_ASSIST_SECRET_WORD_IS_NULL", "�������� 'AssistSecretWord' ������ ���� ����������");

/* Mail */
define("NETCAT_MODULE_PAYMENT_MAIL_ERROR_SIGNATURE_IS_NOT_VALID", "������������ �������� 'Signature'");
define("NETCAT_MODULE_PAYMENT_MAIL_ERROR_SHOP_ID", "�������� 'MailShopID' ������ ���� ������");
define("NETCAT_MODULE_PAYMENT_MAIL_ERROR_SECRET_KEY_IS_NULL", "�������� 'MailSecretKey' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_MAIL_ERROR_HASH_IS_NULL", "�������� 'MailHash' ������ ���� ����������");

/* Paymaster */
define("NETCAT_MODULE_PAYMENT_PAYMASTER_ERROR_MERCHANTID_IS_NOT_VALID", "������������ �������� 'LMI_MERCHANT_ID'");
define("NETCAT_MODULE_PAYMENT_PAYMASTER_ERROR_LMI_PAYMENT_DESC_IS_LONG", "����� ��������� 'LMI_PAYMENT_DESC' ������ ���� ������ 255");
define("NETCAT_MODULE_PAYMENT_PAYMASTER_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ �������� 'LMI_HASH'");

/* Payonline */
define("NETCAT_MODULE_PAYMENT_PAYONLINE_ERROR_MERCHANT_ID", "�������� 'MerchantId' ������ ���� ������");
define("NETCAT_MODULE_PAYMENT_PAYONLINE_ERROR_PRIVATE_SECURITY_KEY_IS_NULL", "�������� 'PrivateSecurityKey' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_PAYONLINE_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ �������� 'SecurityKey'");

/* Paypal */
define("NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_SOME_PARAMETRS_ARE_NOT_VALID", "������������ ���������");
define("NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_PAYPAL_MAIL_IS_NOT_VALID", "������������ ����� ����������� �����");
define("NETCAT_MODULE_PAYMENT_PAYPAL_ERROR_PAYPAL_IPN_NOT_VERIFIED", "IPN-������ �� ���������� PayPal");

/* Platidoma */
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_PGSHOPID_IS_NOT_VALID", "�������� 'pd_shop_id' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_PGLOGIN_IS_NOT_VALID", "�������� 'pd_login' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_PGGATEPASSWORD_IS_NOT_VALID", "�������� 'pd_gate_password' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_PRIVATE_SECURITY_KEY_IS_NULL", "�������� 'PrivateSecurityKey' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_PRIVATE_KEY_IS_NOT_VALID", "������������ �������� 'pd_rnd'");
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ �������� 'pd_sign'");
define("NETCAT_MODULE_PAYMENT_PLATIDOMA_ERROR_ORDER_ID_IS_LONG", "����� ��������� 'OrderId' ������ ���� ������ 50");

/* QIWI */
define("NETCAT_MODULE_PAYMENT_QIWI_ERROR_AMOUNT_TOO_LARGE", "������������ ����� ������� � 15 000 ������");
define("NETCAT_MODULE_PAYMENT_QIWI_ERROR_EMPTY_SETTING", "�� ����� �������� %s � ���������� �������� �������");
define("NETCAT_MODULE_PAYMENT_QIWI_ERROR_WRONG_PHONE", "�������� ������ ������ QIWI-��������");
define("NETCAT_MODULE_PAYMENT_QIWI_SET_PHONE", "����������, ������� ����� ������ QIWI-�������� � �������: +71234567890 (���� � ��� ��� QIWI-��������, �� ����� ������ �������������)");

/* Robokassa */
define("NETCAT_MODULE_PAYMENT_ROBOKASSA_ERROR_MRCHLOGIN_IS_NOT_VALID", "������������ �������� 'MrchLogin'");
define("NETCAT_MODULE_PAYMENT_ROBOKASSA_ERROR_INVID_IS_NOT_VALID", "�������� 'InvId' ������ ���� ������");
define("NETCAT_MODULE_PAYMENT_ROBOKASSA_ERROR_INVDESC_ID_IS_LONG", "����� ��������� 'InvDesc' ������ ���� ������ 100");
define("NETCAT_MODULE_PAYMENT_ROBOKASSA_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ �������� 'SignatureValue'");

/* Webmoney */
define("NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_PURSE_IS_NOT_VALID", "������������ �������� 'LMI_PAYEE_PURSE'");
define("NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ �������� 'LMI_HASH'");
define("NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_DESCRIPTION_IS_LONG", "����� �������� ����� ������ ���� ����� 255");
define("NETCAT_MODULE_PAYMENT_WEBMONEY_ERROR_UNSUPPORTED_HASH_METHOD", "�������������� �������� ������������ ����������� ������� �� ��������������");

/* Yandex_Email */
define("NETCAT_MODULE_PAYMENT_YANDEX_EMAIL_ERROR_RECEIVER", "�������� 'Receiver' ������ ���� ����������");

/* Yandex CPP */
define("NETCAT_MODULE_PAYMENT_YANDEX_CPP_ERROR_SHOPID_IS_NOT_VALID", "������������ �������� 'shopId'");
define("NETCAT_MODULE_PAYMENT_YANDEX_CPP_ERROR_SCID_IS_NOT_VALID", "������������ �������� 'scid'");
define("NETCAT_MODULE_PAYMENT_YANDEX_CPP_ERROR_SHOP_PASSWORD_IS_NOT_VALID", "������������ �������� 'shopPassword'");
define("NETCAT_MODULE_PAYMENT_YANDEX_CPP_ERROR_ORDER_ID_IS_NOT_VALID", "������������ �������� 'orderNumber'");
define("NETCAT_MODULE_PAYMENT_YANDEX_CPP_ERROR_AMOUNT", "�������� 'Amount' ������ ���� ������");
define("NETCAT_MODULE_PAYMENT_YANDEX_CPP_ERROR_PRIVATE_SECURITY_IS_NOT_VALID", "������������ �������� 'md5'");

/* RBK Money */
define("NETCAT_MODULE_PAYMENT_RBK_ERROR_ESHOPID_IS_NOT_VALID", "������������ �������� 'eshopID'");
define("NETCAT_MODULE_PAYMENT_RBK_ERROR_AMOUNT", "�������� 'Amount' ������ ���� ������");
define("NETCAT_MODULE_PAYMENT_RBK_ERROR_ORDER_ID_IS_NOT_VALID", "������������ �������� 'orderID'");

/* Platron */
define("NETCAT_MODULE_PAYMENT_PLATRON_ERROR_MERCHANT_ID_IS_NOT_VALID", "������������ �������� 'merchant_id'");
define("NETCAT_MODULE_PAYMENT_PLATRON_ERROR_SECRET_KEY_IS_NOT_VALID", "������������ �������� 'secret_key'");
define("NETCAT_MODULE_PAYMENT_PLATRON_ERROR_SIGN_IS_NOT_VALID", "������������ �������");

/* PayAnyWay */
define("NETCAT_MODULE_PAYMENT_PAYANYWAY_ERROR_MNT_ID_IS_NOT_VALID", "������������ �������� 'MNT_ID'");

/* PSBank */
define("NETCAT_MODULE_PAYMENT_PSBANK_ERROR_INVALID_HMAC", "������������ �������� HMAC ������ (�������� 'P_SIGN')");

/* CloudPayments */
define("NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_ERROR_PUBLIC_ID_IS_NULL", "�������� 'public_id' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_ERROR_SECRET_KEY_IS_NULL", "�������� 'secret_key' ������ ���� ����������");
define("NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_ORDER", "�����");
define("NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_BUYER", "����������");
define("NETCAT_MODULE_PAYMENT_CLOUDPAYMENTS_PAYMENT_AUTHORIZED", "CloudPayments: ������ �����������");
