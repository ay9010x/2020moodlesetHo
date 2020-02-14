<?php



require_once(__DIR__ . '/../../config.php');

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$usercontext = context_user::instance($user->id);
$coursecontext = context_course::instance($course->id);
$systemcontext = context_system::instance();

$baseurl = new moodle_url('/admin/roles/usersroles.php', array('userid'=>$userid, 'courseid'=>$courseid));

$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('admin');

if ($course->id == SITEID) {
    require_login();
    $PAGE->set_context($usercontext);
} else {
    require_login($course);
    $PAGE->set_context($coursecontext);
}

$canview = has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride',
        'moodle/role:override', 'moodle/role:manage'), $usercontext);
if (!$canview) {
    print_error('nopermissions', 'error', '', get_string('checkpermissions', 'core_role'));
}

if ($userid != $USER->id) {
            $PAGE->navigation->extend_for_user($user);
}
if ($course->id != $SITE->id || $userid != $USER->id) {
            $PAGE->navbar->includesettingsbase = true;
}

$sql = "SELECT ra.id, ra.userid, ra.contextid, ra.roleid, ra.component, ra.itemid, c.path
          FROM {role_assignments} ra
          JOIN {context} c ON ra.contextid = c.id
          JOIN {role} r ON ra.roleid = r.id
         WHERE ra.userid = ?
      ORDER BY contextlevel DESC, contextid ASC, r.sortorder ASC";
$roleassignments = $DB->get_records_sql($sql, array($user->id));

$allroles = role_fix_names(get_all_roles());

$requiredcontexts = array();
foreach ($roleassignments as $ra) {
    $requiredcontexts = array_merge($requiredcontexts, explode('/', trim($ra->path, '/')));
}
$requiredcontexts = array_unique($requiredcontexts);

if ($requiredcontexts) {
    list($sqlcontexttest, $contextparams) = $DB->get_in_or_equal($requiredcontexts);
    $contexts = get_sorted_contexts('ctx.id ' . $sqlcontexttest, $contextparams);
} else {
    $contexts = array();
}

foreach ($contexts as $conid => $con) {
    $contexts[$conid]->children = array();
    $contexts[$conid]->roleassignments = array();
}

foreach ($contexts as $conid => $con) {
    $context = context::instance_by_id($conid);
    $parentcontext = $context->get_parent_context();
    if ($parentcontext) {
        $contexts[$parentcontext->id]->children[] = $conid;
    }
}

foreach ($roleassignments as $ra) {
    $contexts[$ra->contextid]->roleassignments[$ra->roleid] = $ra;
}

$assignableroles = get_assignable_roles($usercontext, ROLENAME_BOTH);
$overridableroles = get_overridable_roles($usercontext, ROLENAME_BOTH);

$fullname = fullname($user, has_capability('moodle/site:viewfullnames', $coursecontext));
$straction = get_string('thisusersroles', 'core_role');
$title = get_string('xroleassignments', 'core_role', $fullname);

$PAGE->set_title($title);
if ($courseid == SITEID) {
    $PAGE->set_heading($fullname);
} else {
    $PAGE->set_heading($course->fullname.': '.$fullname);
}
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');

if (!$roleassignments) {
    echo '<p>', get_string('noroleassignments', 'core_role'), '</p>';
} else {
    print_report_tree($systemcontext->id, $contexts, $systemcontext, $fullname, $allroles);
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

function print_report_tree($contextid, $contexts, $systemcontext, $fullname, $allroles) {
    global $CFG, $OUTPUT;

        static $stredit = null, $strcheckpermissions, $globalroleassigner, $assignurl, $checkurl;
    if (is_null($stredit)) {
        $stredit = get_string('edit');
        $strcheckpermissions = get_string('checkpermissions', 'core_role');
        $globalroleassigner = has_capability('moodle/role:assign', $systemcontext);
        $assignurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/assign.php';
        $checkurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/check.php';
    }

        $context = context::instance_by_id($contextid);

        echo $OUTPUT->heading(html_writer::link($context->get_url(), $context->get_context_name()),
            4, 'contextname');

        foreach ($contexts[$contextid]->roleassignments as $ra) {
        $role = $allroles[$ra->roleid];

        $value = $ra->contextid . ',' . $ra->roleid;
        $inputid = 'unassign' . $value;

        echo '<p>';
        echo $role->localname;
        if (has_capability('moodle/role:assign', $context)) {
            $raurl = $assignurl . '?contextid=' . $ra->contextid . '&amp;roleid=' .
                    $ra->roleid . '&amp;removeselect[]=' . $ra->userid;
            $churl = $checkurl . '?contextid=' . $ra->contextid . '&amp;reportuser=' . $ra->userid;
            if ($context->contextlevel == CONTEXT_USER) {
                $raurl .= '&amp;userid=' . $context->instanceid;
                $churl .= '&amp;userid=' . $context->instanceid;
            }
            $a = new stdClass;
            $a->fullname = $fullname;
            $a->contextlevel = $context->get_level_name();
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                $strgoto = get_string('gotoassignsystemroles', 'core_role');
                $strcheck = get_string('checksystempermissionsfor', 'core_role', $a);
            } else {
                $strgoto = get_string('gotoassignroles', 'core_role', $a);
                $strcheck = get_string('checkuserspermissionshere', 'core_role', $a);
            }
            echo ' <a title="' . $strgoto . '" href="' . $raurl . '"><img class="iconsmall" src="' .
                    $OUTPUT->pix_url('t/edit') . '" alt="' . $stredit . '" /></a> ';
            echo ' <a title="' . $strcheck . '" href="' . $churl . '"><img class="iconsmall" src="' .
                    $OUTPUT->pix_url('t/preview') . '" alt="' . $strcheckpermissions . '" /></a> ';
            echo "</p>\n";
        }
    }

        if (!empty($contexts[$contextid]->children)) {
        echo '<ul>';
        foreach ($contexts[$contextid]->children as $childcontextid) {
            echo '<li>';
            print_report_tree($childcontextid, $contexts, $systemcontext, $fullname, $allroles);
            echo '</li>';
        }
        echo '</ul>';
    }
}
