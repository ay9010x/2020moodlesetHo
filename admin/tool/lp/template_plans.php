<?php



require(__DIR__ . '/../../../config.php');

$id = required_param('id', PARAM_INT);
$pagecontextid = required_param('pagecontextid', PARAM_INT);  
require_login(0, false);
\core_competency\api::require_enabled();

$template = \core_competency\api::read_template($id);
$context = $template->get_context();
$canreadtemplate = $template->can_read();
$canmanagetemplate = $template->can_manage();
if (!$canreadtemplate) {
    throw new required_capability_exception($context, 'moodle/competency:templateview', 'nopermissions', '');
}

$url = new moodle_url('/admin/tool/lp/template_plans.php', array(
    'id' => $id,
    'pagecontextid' => $pagecontextid
));
list($title, $subtitle) = \tool_lp\page_helper::setup_for_template($pagecontextid, $url, $template,
    get_string('userplans', 'core_competency'));

$form = new \tool_lp\form\template_plans($url->out(false));
if ($canmanagetemplate && ($data = $form->get_data()) && !empty($data->users)) {
    $i = 0;
    foreach ($data->users as $userid) {
        $result = \core_competency\api::create_plan_from_template($template->get_id(), $userid);
        if ($result) {
            $i++;
        }
    }
    if ($i == 0) {
        $notification = get_string('noplanswerecreated', 'tool_lp');
    } else if ($i == 1) {
        $notification = get_string('oneplanwascreated', 'tool_lp');
    } else {
        $notification = get_string('aplanswerecreated', 'tool_lp', $i);
    }
    redirect($url, $notification);
}

$output = $PAGE->get_renderer('tool_lp');
echo $output->header();
echo $output->heading($title);
echo $output->heading($subtitle, 3);

if ($canmanagetemplate) {
    if (!$template->get_visible()) {
                echo $output->notify_message(get_string('cannotcreateuserplanswhentemplatehidden', 'tool_lp'));
    } else if ($template->get_duedate() > 0 && $template->get_duedate() < time() + 900) {
                echo $output->notify_message(get_string('cannotcreateuserplanswhentemplateduedateispassed', 'tool_lp'));
    } else {
        echo $form->display();
    }
}

$page = new \tool_lp\output\template_plans_page($template, $url);
echo $output->render($page);
echo $output->footer();
