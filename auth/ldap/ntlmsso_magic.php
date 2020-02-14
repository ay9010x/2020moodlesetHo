<?php

define('NO_MOODLE_COOKIES', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$PAGE->https_required();

$PAGE->set_context(context_system::instance());

$authsequence = get_enabled_auth_plugins(true); if (!in_array('ldap', $authsequence, true)) {
    print_error('ldap_isdisabled', 'auth');
}

$authplugin = get_auth_plugin('ldap');
if (empty($authplugin->config->ntlmsso_enabled)) {
    print_error('ntlmsso_isdisabled', 'auth_ldap');
}

$sesskey = required_param('sesskey', PARAM_RAW);
$file = $CFG->dirroot.'/pix/spacer.gif';

if ($authplugin->ntlmsso_magic($sesskey) && file_exists($file)) {
    if (!empty($authplugin->config->ntlmsso_ie_fastpath)) {
        if (core_useragent::is_ie()) {
                        redirect($CFG->httpswwwroot.'/auth/ldap/ntlmsso_finish.php');
        }
    }

            header('Content-Type: image/gif');
    header('Content-Length: '.filesize($file));

        $handle = fopen($file, 'r');
    fpassthru($handle);
    fclose($handle);
    exit;
} else {
    print_error('ntlmsso_iwamagicnotenabled', 'auth_ldap');
}


