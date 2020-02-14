<?php



require_once(__DIR__ . '/../../../config.php');

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$userid = optional_param('userid', $USER->id, PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$returntype = optional_param('return', null, PARAM_ALPHA);

$url = new moodle_url('/admin/tool/lp/user_evidence_edit.php', array('id' => $id, 'userid' => $userid, 'return' => $returntype));

$userevidence = null;
if (empty($id)) {
    $pagetitle = get_string('addnewuserevidence', 'tool_lp');
    list($title, $subtitle, $returnurl) = \tool_lp\page_helper::setup_for_user_evidence($userid, $url, null,
        $pagetitle, $returntype);

} else {
    $userevidence = \core_competency\api::read_user_evidence($id);

        if ($userid != $userevidence->get_userid()) {
        throw new coding_exception('Inconsistency between the userid parameter and the userid of the plan.');
    }

    $pagetitle = get_string('edituserevidence', 'tool_lp');
    list($title, $subtitle, $returnurl) = \tool_lp\page_helper::setup_for_user_evidence($userid, $url, $userevidence,
        $pagetitle, $returntype);
}

$context = $PAGE->context;

$fileareaoptions = array('subdirs' => false);
$customdata = array(
    'fileareaoptions' => $fileareaoptions,
    'persistent' => $userevidence,
    'userid' => $userid,
);

if ($userevidence != null) {
    if (!$userevidence->can_manage()) {
        throw new required_capability_exception($context, 'moodle/competency:userevidencemanage', 'nopermissions', '');
    }
    $customdata['evidence'] = $userevidence;

} else if (!\core_competency\user_evidence::can_manage_user($userid)) {
    throw new required_capability_exception($context, 'moodle/competency:userevidencemanage', 'nopermissions', '');
}

$form = new \tool_lp\form\user_evidence($url->out(false), $customdata);
if ($form->is_cancelled()) {
    redirect($returnurl);
}

$itemid = null;
if ($userevidence) {
    $itemid = $userevidence->get_id();
}

$draftitemid = file_get_submitted_draft_itemid('files');
file_prepare_draft_area($draftitemid, $context->id, 'core_competency', 'userevidence', $itemid, $fileareaoptions);
$form->set_data((object) array('files' => $draftitemid));

if ($data = $form->get_data()) {
    require_sesskey();
    $draftitemid = $data->files;
    unset($data->files);

    if (empty($userevidence)) {
        $userevidence = \core_competency\api::create_user_evidence($data, $draftitemid);
        $returnurl = new moodle_url('/admin/tool/lp/user_evidence.php', ['id' => $userevidence->get_id()]);
        $returnmsg = get_string('userevidencecreated', 'tool_lp');
    } else {
        \core_competency\api::update_user_evidence($data, $draftitemid);
        $returnmsg = get_string('userevidenceupdated', 'tool_lp');
    }
    redirect($returnurl, $returnmsg, null, \core\output\notification::NOTIFY_SUCCESS);
}

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($title);
if (!empty($subtitle)) {
    echo $output->heading($subtitle, 3);
}

$form->display();

echo $output->footer();
