<?php





require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');

$step   = optional_param('step', 'verify', PARAM_ALPHA);
$hostid = required_param('hostid', PARAM_INT);


require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$mnet = get_mnet_environment();

$PAGE->set_url('/admin/mnet/delete.php');
admin_externalpage_setup('mnetpeer' . $hostid);

require_sesskey();

$mnet_peer = new mnet_peer();
$mnet_peer->set_id($hostid);

if ('verify' == $step) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('deleteaserver', 'mnet'));
    if ($live_users = $mnet_peer->count_live_sessions() > 0) {
        echo $OUTPUT->notification(get_string('usersareonline', 'mnet', $live_users));
    }
    $yesurl = new moodle_url('/admin/mnet/delete.php', array('hostid' => $mnet_peer->id, 'step' => 'delete'));
    $nourl = new moodle_url('/admin/mnet/peers.php');
    echo $OUTPUT->confirm(get_string('reallydeleteserver', 'mnet')  . ': ' .  $mnet_peer->name, $yesurl, $nourl);
    echo $OUTPUT->footer();
} elseif ('delete' == $step) {
    $mnet_peer->delete();
    redirect(new moodle_url('/admin/mnet/peers.php'), get_string('hostdeleted', 'mnet'), 5);
}
