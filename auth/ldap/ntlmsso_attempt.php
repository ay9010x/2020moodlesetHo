<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$PAGE->https_required();

$PAGE->set_url('/auth/ldap/ntlmsso_attempt.php');
$PAGE->set_context(context_system::instance());

$site = get_site();

$authsequence = get_enabled_auth_plugins(true); if (!in_array('ldap', $authsequence, true)) {
    print_error('ldap_isdisabled', 'auth');
}

$authplugin = get_auth_plugin('ldap');
if (empty($authplugin->config->ntlmsso_enabled)) {
    print_error('ntlmsso_isdisabled', 'auth_ldap');
}

$sesskey = sesskey();

$loginsite = get_string("loginsite");
$PAGE->navbar->add($loginsite);
$PAGE->set_title("$site->fullname: $loginsite");
$PAGE->set_heading($site->fullname);
echo $OUTPUT->header();

$msg = '<p>'.get_string('ntlmsso_attempting', 'auth_ldap').'</p>'
    . '<img width="1", height="1" '
    . ' src="' . $CFG->httpswwwroot . '/auth/ldap/ntlmsso_magic.php?sesskey='
    . $sesskey . '" />';
redirect($CFG->httpswwwroot . '/auth/ldap/ntlmsso_finish.php', $msg, 3);
