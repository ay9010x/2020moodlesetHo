<?php





if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); }

$strmnetservices   = get_string('mnetservices', 'mnet');
$strmnetedithost   = get_string('reviewhostdetails', 'mnet');

$tabs = array();
if (isset($mnet_peer->id) && $mnet_peer->id > 0) {
    $tabs[] = new tabobject('mnetdetails', 'peers.php?step=update&amp;hostid='.$mnet_peer->id, $strmnetedithost, $strmnetedithost, false);
    $tabs[] = new tabobject('mnetservices', 'services.php?hostid='.$mnet_peer->id, $strmnetservices, $strmnetservices, false);
    $tabs[] = new tabobject('mnetprofilefields', 'profilefields.php?hostid=' . $mnet_peer->id, get_string('profilefields', 'mnet'), get_string('profilefields', 'mnet'), false);
} else {
    $tabs[] = new tabobject('mnetdetails', '#', $strmnetedithost, $strmnetedithost, false);
}
print_tabs(array($tabs), $currenttab);
