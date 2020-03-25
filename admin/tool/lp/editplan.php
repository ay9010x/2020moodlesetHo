<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$userid = optional_param('userid', false, PARAM_INT);
$id = optional_param('id', false, PARAM_INT);
$returntype = optional_param('return', null, PARAM_ALPHA);

require_login(0, false);
\core_competency\api::require_enabled();

$url = new moodle_url('/admin/tool/lp/editplan.php', array('id' => $id, 'userid' => $userid, 'return' => $returntype));

$plan = null;
if (empty($id)) {
    $pagetitle = get_string('addnewplan', 'tool_lp');
    list($title, $subtitle, $returnurl) = \tool_lp\page_helper::setup_for_plan($userid, $url, null, $pagetitle, $returntype);
} else {
    $plan = \core_competency\api::read_plan($id);

        if ($userid != $plan->get_userid()) {
        throw new coding_exception('Inconsistency between the userid parameter and the userid of the plan');
    }

    $pagetitle = get_string('editplan', 'tool_lp');
    list($title, $subtitle, $returnurl) = \tool_lp\page_helper::setup_for_plan($userid, $url, $plan, $pagetitle, $returntype);
}

$output = $PAGE->get_renderer('tool_lp');

$customdata = array('userid' => $userid, 'context' => $PAGE->context, 'persistent' => $plan);

$cancreate = \core_competency\plan::can_manage_user_draft($userid) || \core_competency\plan::can_manage_user($userid);

if ($plan != null) {
    if (!$plan->can_manage()) {
        throw new required_capability_exception($PAGE->context, 'moodle/competency:planmanage', 'nopermissions', '');
    }
    if (!$plan->can_be_edited()) {
        throw new coding_exception('Completed plan can not be edited');
    }
} else if (!$cancreate) {
    throw new required_capability_exception($PAGE->context, 'moodle/competency:planmanage', 'nopermissions', '');
}

$form = new \tool_lp\form\plan($url->out(false), $customdata);
if ($form->is_cancelled()) {
    redirect($returnurl);
}

$data = $form->get_data();

if ($data) {
    if (empty($data->id)) {
        $plan = \core_competency\api::create_plan($data);
        $returnurl = new moodle_url('/admin/tool/lp/plan.php', ['id' => $plan->get_id()]);
        $returnmsg = get_string('plancreated', 'tool_lp');
    } else {
        \core_competency\api::update_plan($data);
        $returnmsg = get_string('planupdated', 'tool_lp');
    }
    redirect($returnurl, $returnmsg, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $output->header();
echo $output->heading($title);
if (!empty($subtitle)) {
    echo $output->heading($subtitle, 3);
}

$form->display();

echo $output->footer();
