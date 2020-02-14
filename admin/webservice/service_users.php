<?php



require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/webservice/lib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

$id = required_param('id', PARAM_INT);

admin_externalpage_setup('externalserviceusers');

$PAGE->set_url('/' . $CFG->admin . '/webservice/service_users.php', array('id' => $id));
$node = $PAGE->settingsnav->find('externalservices', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
$PAGE->navbar->add(get_string('serviceusers', 'webservice'),
        new moodle_url('/' . $CFG->admin . '/webservice/service_users.php', array('id' => $id)));

$webservicemanager = new webservice();

$potentialuserselector = new service_user_selector('addselect',
                array('serviceid' => $id, 'displayallowedusers' => 0));
$alloweduserselector = new service_user_selector('removeselect',
                array('serviceid' => $id, 'displayallowedusers' => 1));

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {
        foreach ($userstoassign as $adduser) {
            $serviceuser = new stdClass();
            $serviceuser->externalserviceid = $id;
            $serviceuser->userid = $adduser->id;
            $webservicemanager->add_ws_authorised_user($serviceuser);

            $params = array(
                'objectid' => $serviceuser->externalserviceid,
                'relateduserid' => $serviceuser->userid
            );
            $event = \core\event\webservice_service_user_added::create($params);
            $event->trigger();
        }
        $potentialuserselector->invalidate_selected_users();
        $alloweduserselector->invalidate_selected_users();
    }
}

if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $alloweduserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            $webservicemanager->remove_ws_authorised_user($removeuser, $id);

            $params = array(
                'objectid' => $id,
                'relateduserid' => $removeuser->id
            );
            $event = \core\event\webservice_service_user_removed::create($params);
            $event->trigger();
        }
        $potentialuserselector->invalidate_selected_users();
        $alloweduserselector->invalidate_selected_users();
    }
}
$renderer = $PAGE->get_renderer('core', 'webservice');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('selectauthorisedusers', 'webservice'), 3, 'main');
$selectoroptions = new stdClass();
$selectoroptions->serviceid = $id;
$selectoroptions->alloweduserselector = $alloweduserselector;
$selectoroptions->potentialuserselector = $potentialuserselector;
echo $renderer->admin_authorised_user_selector($selectoroptions);

$allowedusers = $webservicemanager->get_ws_authorised_users($id);
$usersmissingcaps = $webservicemanager->get_missing_capabilities_by_users($allowedusers, $id);

foreach ($allowedusers as &$alloweduser) {
    if (!is_siteadmin($alloweduser->id) and array_key_exists($alloweduser->id, $usersmissingcaps)) {
        $alloweduser->missingcapabilities = implode(', ', $usersmissingcaps[$alloweduser->id]);
    }
}

if (!empty($allowedusers)) {
    $renderer = $PAGE->get_renderer('core', 'webservice');
    echo $OUTPUT->heading(get_string('serviceuserssettings', 'webservice'), 3, 'main');
    echo $renderer->admin_authorised_user_list($allowedusers, $id);
}

echo $OUTPUT->footer();
