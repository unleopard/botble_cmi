<?php

if (!defined('CMI_PAYMENT_METHOD_NAME')) {
    define('CMI_PAYMENT_METHOD_NAME', 'cmi');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', url('/'));
}
if (!defined('CMI_URL_CALLBACK')) {
    define('CMI_URL_CALLBACK', BASE_URL . '/checkout/cmi/callback');
}
if (!defined('CMI_URL_SUCCESS')) {
    define('CMI_URL_SUCCESS', BASE_URL . '/checkout/cmi/success');
}

if (!defined('CMI_URL_FAIL')) {
    define('CMI_URL_FAIL', BASE_URL . '/checkout/cmi/fail');
}

if (!defined('CMI_URL_DEV')) {
    define('CMI_URL_DEV', 'https://testpayment.cmi.co.ma/fim/est3Dgate');
}

if (!defined('CMI_URL_PROD')) {
    define('CMI_URL_PROD', 'https://payment.cmi.co.ma/fim/est3Dgate');
}
