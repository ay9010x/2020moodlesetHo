<?php




define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');

require_login(null, false);

$branchtype = required_param('type', PARAM_INT);
if ($branchtype !== navigation_node::TYPE_SITE_ADMIN) {
    throw new coding_exception('Incorrect node type passed');
}

ajax_capture_output();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/lib/ajax/getsiteadminbranch.php', array('type'=>$branchtype));

$sitenavigation = new settings_navigation_ajax($PAGE);

$converter = new navigation_json();
$branch = $sitenavigation->get('root');

ajax_check_captured_output();
echo $converter->convert($branch);
