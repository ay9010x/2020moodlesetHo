<?php



require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$ruleid = optional_param('ruleid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$status = optional_param('status', 0, PARAM_BOOL);

if (empty($courseid)) {
    require_login();
    $context = context_system::instance();
    $coursename = format_string($SITE->fullname, true, array('context' => $context));
    $PAGE->set_context($context);
} else {
    $course = get_course($courseid);
    require_login($course);
    $context = context_course::instance($course->id);
    $coursename = format_string($course->fullname, true, array('context' => $context));
}

require_capability('tool/monitor:managerules', $context);

$manageurl = new moodle_url("/admin/tool/monitor/managerules.php", array('courseid' => $courseid));
$PAGE->set_url($manageurl);
$PAGE->set_pagelayout('report');
$PAGE->set_title($coursename);
$PAGE->set_heading($coursename);

if (empty($courseid)) {
    admin_externalpage_setup('toolmonitorrules', '', null, '', array('pagelayout' => 'report'));
}

if (!empty($action) && $action == 'changestatus') {
    require_sesskey();
    require_capability('tool/monitor:managetool', context_system::instance());
        set_config('enablemonitor', $status, 'tool_monitor');
    redirect(new moodle_url('/admin/tool/monitor/managerules.php', array('courseid' => 0)));
}

if (!empty($action) && $ruleid) {
    require_sesskey();

        if (!$rule = $DB->get_record('tool_monitor_rules', array('id' => $ruleid), '*', IGNORE_MISSING)) {
        redirect(new moodle_url('/admin/tool/monitor/managerules.php', array('courseid' => $courseid)));
    }

    echo $OUTPUT->header();
    $rule = \tool_monitor\rule_manager::get_rule($rule);
    switch ($action) {
        case 'copy':
                        $rule->duplicate_rule($courseid);
            echo $OUTPUT->notification(get_string('rulecopysuccess', 'tool_monitor'), 'notifysuccess');
            break;
        case 'delete':
            if ($rule->can_manage_rule()) {
                $confirmurl = new moodle_url($CFG->wwwroot. '/admin/tool/monitor/managerules.php',
                    array('ruleid' => $ruleid, 'courseid' => $courseid, 'action' => 'delete',
                        'confirm' => true, 'sesskey' => sesskey()));
                $cancelurl = new moodle_url($CFG->wwwroot. '/admin/tool/monitor/managerules.php',
                    array('courseid' => $courseid));
                if ($confirm) {
                    $rule->delete_rule();
                    echo $OUTPUT->notification(get_string('ruledeletesuccess', 'tool_monitor'), 'notifysuccess');
                } else {
                    $strconfirm = get_string('ruleareyousure', 'tool_monitor', $rule->get_name($context));
                    if ($numberofsubs = $DB->count_records('tool_monitor_subscriptions', array('ruleid' => $ruleid))) {
                        $strconfirm .= '<br />';
                        $strconfirm .= get_string('ruleareyousureextra', 'tool_monitor', $numberofsubs);
                    }
                    echo $OUTPUT->confirm($strconfirm, $confirmurl, $cancelurl);
                    echo $OUTPUT->footer();
                    exit();
                }
            } else {
                                throw new moodle_exception('rulenopermissions', 'tool_monitor', $manageurl, $action);
            }
            break;
        default:
    }
} else {
    echo $OUTPUT->header();
}

echo $OUTPUT->heading(get_string('managerules', 'tool_monitor'));
$status = get_config('tool_monitor', 'enablemonitor');
$help = new help_icon('enablehelp', 'tool_monitor');

if ($status) {
    if (has_capability('tool/monitor:managetool', context_system::instance())) {
                echo get_string('monitorenabled', 'tool_monitor');
        $disableurl = new moodle_url("/admin/tool/monitor/managerules.php",
                array('courseid' => $courseid, 'action' => 'changestatus', 'status' => 0, 'sesskey' => sesskey()));
        echo ' ' . html_writer::link($disableurl, get_string('disable'));
        echo $OUTPUT->render($help);
    }
} else {
    echo get_string('monitordisabled', 'tool_monitor');
    if (has_capability('tool/monitor:managetool', context_system::instance())) {
        $enableurl = new moodle_url("/admin/tool/monitor/managerules.php",
                array('courseid' => $courseid, 'action' => 'changestatus', 'status' => 1, 'sesskey' => sesskey()));
        echo ' ' . html_writer::link($enableurl, get_string('enable'));
        echo $OUTPUT->render($help);
    } else {
        echo ' ' . get_string('contactadmin', 'tool_monitor');
    }
    echo $OUTPUT->footer();     exit();
}

$renderable = new \tool_monitor\output\managerules\renderable('toolmonitorrules', $manageurl, $courseid);
$renderer = $PAGE->get_renderer('tool_monitor', 'managerules');
echo $renderer->render($renderable);
if (has_capability('tool/monitor:subscribe', $context)) {
    $manageurl = new moodle_url("/admin/tool/monitor/index.php", array('courseid' => $courseid));
    echo $renderer->render_subscriptions_link($manageurl);
}
echo $OUTPUT->footer();
