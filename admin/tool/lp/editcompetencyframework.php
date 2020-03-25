<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$returntype = optional_param('return', null, PARAM_TEXT);
$pagecontextid = required_param('pagecontextid', PARAM_INT);  
$framework = null;
if (!empty($id)) {
        $framework = new \core_competency\competency_framework($id);
    $context = $framework->get_context();
} else {
    $context = context::instance_by_id($pagecontextid);
}

require_login();
\core_competency\api::require_enabled();
require_capability('moodle/competency:competencymanage', $context);

list($pagetitle, $pagesubtitle, $url, $frameworksurl) = tool_lp\page_helper::setup_for_framework($id,
        $pagecontextid, $framework, $returntype);
$output = $PAGE->get_renderer('tool_lp');
$form = new \tool_lp\form\competency_framework($url->out(false), array('context' => $context, 'persistent' => $framework));

if ($form->is_cancelled()) {
    redirect($frameworksurl);
} else if ($data = $form->get_data()) {
    if (empty($data->id)) {
                $data->contextid = $context->id;
        $framework = \core_competency\api::create_framework($data);
        $frameworkmanageurl = new moodle_url('/admin/tool/lp/competencies.php', array(
            'pagecontextid' => $pagecontextid,
            'competencyframeworkid' => $framework->get_id()
        ));
        $messagesuccess = get_string('competencyframeworkcreated', 'tool_lp');
        redirect($frameworkmanageurl, $messagesuccess, 0, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        \core_competency\api::update_framework($data);
        $messagesuccess = get_string('competencyframeworkupdated', 'tool_lp');
        redirect($frameworksurl, $messagesuccess, 0, \core\output\notification::NOTIFY_SUCCESS);
    }
}

echo $output->header();
echo $output->heading($pagetitle, 2);
echo $output->heading($pagesubtitle, 3);
$form->display();
echo $output->footer();
