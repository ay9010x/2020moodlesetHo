<?php



require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once $CFG->dirroot . '/mnet/xmlrpc/client.php';

$token         = required_param('token',    PARAM_BASE64);
$remotewwwroot = required_param('idp',      PARAM_URL);
$wantsurl      = required_param('wantsurl', PARAM_LOCALURL);
$wantsremoteurl = optional_param('remoteurl', false, PARAM_BOOL);

$url = new moodle_url('/auth/mnet/jump.php', array('token'=>$token, 'idp'=>$remotewwwroot, 'wantsurl'=>$wantsurl));
if ($wantsremoteurl !== false) $url->param('remoteurl', $wantsremoteurl);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$site = get_site();

if (!is_enabled_auth('mnet')) {
    print_error('mnetdisable');
}

$mnetauth = get_auth_plugin('mnet');
$remotepeer = new mnet_peer();
$remotepeer->set_wwwroot($remotewwwroot);
$localuser = $mnetauth->confirm_mnet_session($token, $remotepeer);

$user = get_complete_user_data('id', $localuser->id, $localuser->mnethostid);
complete_user_login($user);
$mnetauth->update_mnet_session($user, $token, $remotepeer);

if (!empty($localuser->mnet_foreign_host_array)) {
    $USER->mnet_foreign_host_array = $localuser->mnet_foreign_host_array;
}

if ($wantsremoteurl) {
    redirect($remotewwwroot . $wantsurl);
}
redirect($CFG->wwwroot . $wantsurl);


