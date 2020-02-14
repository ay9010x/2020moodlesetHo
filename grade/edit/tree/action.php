<?php



require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';

$courseid = required_param('id', PARAM_INT);
$action   = required_param('action', PARAM_ALPHA);
$eid      = required_param('eid', PARAM_ALPHANUM);

$PAGE->set_url('/grade/edit/tree/action.php', array('id'=>$courseid, 'action'=>$action, 'eid'=>$eid));

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$context = context_course::instance($course->id);

$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url($CFG->wwwroot.'/grade/edit/tree/index.php?id='.$course->id);

$gtree = new grade_tree($courseid, false, false);

if (!$element = $gtree->locate_element($eid)) {
    print_error('invalidelementid', '', $returnurl);
}
$object = $element['object'];
$type   = $element['type'];


switch ($action) {
    case 'hide':
        if ($eid and confirm_sesskey()) {
            if (!has_capability('moodle/grade:manage', $context) and !has_capability('moodle/grade:hide', $context)) {
                print_error('nopermissiontohide', '', $returnurl);
            }
            if ($type == 'grade' and empty($object->id)) {
                $object->insert();
            }
            if (!$object->can_control_visibility()) {
                print_error('componentcontrolsvisibility', 'grades', $returnurl);
            }
            $object->set_hidden(1, true);
        }
        break;

    case 'show':
        if ($eid and confirm_sesskey()) {
            if (!has_capability('moodle/grade:manage', $context) and !has_capability('moodle/grade:hide', $context)) {
                print_error('nopermissiontoshow', '', $returnurl);
            }
            if ($type == 'grade' and empty($object->id)) {
                $object->insert();
            }
            if (!$object->can_control_visibility()) {
                print_error('componentcontrolsvisibility', 'grades', $returnurl);
            }
            $object->set_hidden(0, true);
        }
        break;

    case 'lock':
        if ($eid and confirm_sesskey()) {
            if (!has_capability('moodle/grade:manage', $context) and !has_capability('moodle/grade:lock', $context)) {
                print_error('nopermissiontolock', '', $returnurl);
            }
            if ($type == 'grade' and empty($object->id)) {
                $object->insert();
            }
            $object->set_locked(1, true, true);
        }
        break;

    case 'unlock':
        if ($eid and confirm_sesskey()) {
            if (!has_capability('moodle/grade:manage', $context) and !has_capability('moodle/grade:unlock', $context)) {
                print_error('nopermissiontounlock', '', $returnurl);
            }
            if ($type == 'grade' and empty($object->id)) {
                $object->insert();
            }
            $object->set_locked(0, true, true);
        }
        break;

    case 'resetweights':
        if ($eid && confirm_sesskey()) {

                                    if ($type != 'category' || $object->aggregation != GRADE_AGGREGATE_SUM ||
                    !has_capability('moodle/grade:manage', $context)) {
                print_error('nopermissiontoresetweights', 'grades', $returnurl);
            }

                        $children = $object->get_children();
            foreach ($children as $item) {
                if ($item['type'] == 'category') {
                    $gradeitem = $item['object']->load_grade_item();
                } else {
                    $gradeitem = $item['object'];
                }

                if ($gradeitem->weightoverride == false) {
                    continue;
                }

                $gradeitem->weightoverride = false;
                $gradeitem->update();
            }

                        $object->force_regrading();
        }
}

redirect($returnurl);


