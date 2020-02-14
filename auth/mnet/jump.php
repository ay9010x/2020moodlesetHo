<?php



require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$hostid = optional_param('hostid', '0', PARAM_INT);
$hostwwwroot = optional_param('hostwwwroot', '', PARAM_URL);
$wantsurl = optional_param('wantsurl', '', PARAM_RAW);

$url = new moodle_url('/auth/mnet/jump.php');
if ($hostid !== '0') $url->param('hostid', $hostid);
if ($hostwwwroot !== '') $url->param('hostwwwroot', $hostwwwroot);
if ($wantsurl !== '') $url->param('wantsurl', $wantsurl);
$PAGE->set_url($url);

if (!isloggedin() or isguestuser()) {
    $SESSION->wantsurl = $PAGE->url->out(false);
    redirect(get_login_url());
}

if (!is_enabled_auth('mnet')) {
    print_error('mnetdisable');
}

if (!$hostid) {
    $hostwwwroot = trim($hostwwwroot);
    $hostwwwroot = rtrim($hostwwwroot, '/');

        if (strtolower(substr($hostwwwroot, 0, 4)) != 'http') {
        $hostwwwroot = 'http://'.$hostwwwroot;
    }
    $hostid = $DB->get_field('mnet_host', 'id', array('wwwroot' => $hostwwwroot));
}

$mnetauth = get_auth_plugin('mnet');
$url      = $mnetauth->start_jump_session($hostid, $wantsurl);

if (empty($url)) {
    print_error('DEBUG: Jump session was not started correctly or blank URL returned.'); }
redirect($url);


