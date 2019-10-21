<?php

$NETCAT_FOLDER = realpath(__DIR__ . '/../../../') . '/';
require_once $NETCAT_FOLDER . 'vars.inc.php';
require_once $ROOT_FOLDER . 'connect_io.php';
require_once $nc_core->ADMIN_FOLDER . 'lang/' . $nc_core->lang->detect_lang() . '.php';

$nc_core->modules->load_env();
$nc_auth = nc_auth::get_object();

$act = $nc_core->input->fetch_get_post('act');
$result = null;

switch ($act) {
    case 'check_login':
        $result = $nc_core->user->check_login($nc_core->input->fetch_get_post('login'));
        break;
    case 'auth':
        if (!$AUTH_USER_ID) {
            $AuthPhase = 1;
        }

        $r = 1;
        if ($nc_core->user->captcha_is_required() && !nc_captcha_verify_code($nc_core->input->fetch_get_post('nc_captcha_code'))) {
            $result['user_id'] = false;
            $result['captcha_wrong'] = true;
            $result['captcha_hash'] = nc_captcha_generate_hash();
            nc_captcha_generate_code($result['captcha_hash']);
            $r = 0;
        }

        if ($r && ($result['user_id'] = Authorize())) {
            $serialized_params = $nc_core->input->fetch_get_post('params');
            $nc_auth->check_string_hash($serialized_params, $nc_core->input->fetch_get_post('params_hash'));
            $params = unserialize($serialized_params, false);

            $serialized_template = $nc_core->input->fetch_get_post('template');
            $nc_auth->check_string_hash($serialized_template, $nc_core->input->fetch_get_post('template_hash'));
            $template = unserialize($serialized_template, false);

            $params['ajax'] = 0;
            $result['login'] = ($nc_core->NC_UNICODE ? $current_user[$nc_core->AUTHORIZE_BY] : $nc_core->utf8->win2utf($current_user[$nc_core->AUTHORIZE_BY]));
            $result['auth_block'] = ($nc_core->NC_UNICODE ? $nc_auth->auth_links($params, $template) : $nc_core->utf8->win2utf($nc_auth->auth_links($params, $template)));
        }
        break;
}

echo json_encode($result);