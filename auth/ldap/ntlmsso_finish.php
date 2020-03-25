<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$PAGE->https_required();

$PAGE->set_url('/auth/ldap/ntlmsso_finish.php');
$PAGE->set_context(context_system::instance());

$site = get_site();

$authsequence = get_enabled_auth_plugins(true); if (!in_array('ldap', $authsequence, true)) {
    print_error('ldap_isdisabled', 'auth');
}

$authplugin = get_auth_plugin('ldap');
if (empty($authplugin->config->ntlmsso_enabled)) {
    print_error('ntlmsso_isdisabled', 'auth_ldap');
}

if (!$authplugin->ntlmsso_finish()) {
                $loginsite = get_string("loginsite");
    $PAGE->navbar->add($loginsite);
    $PAGE->set_title("$site->fullname: $loginsite");
    $PAGE->set_heading($site->fullname);
    echo $OUTPUT->header();
    redirect($CFG->httpswwwroot . '/login/index.php?authldap_skipntlmsso=1',
             get_string('ntlmsso_failed','auth_ldap'), 3);
}
