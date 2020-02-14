<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$competencyframeworkid = optional_param('competencyframeworkid', 0, PARAM_INT);
$pagecontextid = required_param('pagecontextid', PARAM_INT);  $parentid = optional_param('parentid', 0, PARAM_INT);

require_login();
\core_competency\api::require_enabled();

if (empty($competencyframeworkid) && empty($id)) {
    throw new coding_exception('Competencyframeworkid param is required');
}

$competencyframework = null;
if (!empty($competencyframeworkid)) {
    $competencyframework = \core_competency\api::read_framework($competencyframeworkid);
}

$competency = null;
if (!empty($id)) {
    $competency = \core_competency\api::read_competency($id);
    if (empty($competencyframework)) {
        $competencyframework = $competency->get_framework();
    }
}

$parent = null;
if ($competency) {
    $parent = $competency->get_parent();
} else if ($parentid) {
    $parent = \core_competency\api::read_competency($parentid);
}

$urloptions = [
    'id' => $id,
    'competencyframeworkid' => $competencyframework->get_id(),
    'parentid' => $parentid,
    'pagecontextid' => $pagecontextid
];
$url = new moodle_url("/admin/tool/lp/editcompetency.php", $urloptions);

list($title, $subtitle, $returnurl) = \tool_lp\page_helper::setup_for_competency($pagecontextid, $url, $competencyframework,
    $competency, $parent);

$formoptions = [
    'competencyframework' => $competencyframework,
    'parent' => $parent,
    'persistent' => $competency,
    'pagecontextid' => $pagecontextid
];
$form = new \tool_lp\form\competency($url->out(false), $formoptions);

if ($form->is_cancelled()) {
    redirect($returnurl);
}

$data = $form->get_data();
if ($data) {
    if (empty($competency)) {
        \core_competency\api::create_competency($data);
        $returnmsg = get_string('competencycreated', 'tool_lp');
    } else {
        \core_competency\api::update_competency($data);
        $returnmsg = get_string('competencyupdated', 'tool_lp');
    }
    redirect($returnurl, $returnmsg, null, \core\output\notification::NOTIFY_SUCCESS);
}

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($title);
echo $output->heading($subtitle, 3);

$form->display();

echo $output->footer();
