<?php



require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';

$courseid = required_param('id', PARAM_INT);                   $userid = optional_param('userid', false, PARAM_INT);
$itemid = optional_param('itemid', false, PARAM_INT);
$type = optional_param('type', false, PARAM_ALPHA);
$action = optional_param('action', false, PARAM_ALPHA);
$newvalue = optional_param('newvalue', false, PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
$context = context_course::instance($course->id);
require_login($course);

switch ($action) {
    case 'update':
        if (!confirm_sesskey()) {
            break;
        }
        require_capability('moodle/grade:edit', $context);

        if (!empty($userid) && !empty($itemid) && $newvalue !== false && !empty($type)) {
                        if (!$grade_item = grade_item::fetch(array('id'=>$itemid, 'courseid'=>$courseid))) {                 print_error('invalidgradeitemid');
            }

            
            $warnings = array();
            $finalvalue = null;
            $finalgrade = null;
            $feedback = null;
            $json_object = new stdClass();
                        if ($type == 'value' || $type == 'scale') {
                $feedback = false;
                $feedbackformat = false;
                if ($grade_item->gradetype == GRADE_TYPE_SCALE) {
                    if ($newvalue == -1) {                         $finalgrade = null;
                    } else {
                        $finalgrade = $newvalue;
                    }
                } else {
                    $finalgrade = unformat_float($newvalue);
                }

                $errorstr = '';
                                if (is_null($finalgrade)) {
                                    } else {
                    $bounded = $grade_item->bounded_grade($finalgrade);
                    if ($bounded > $finalgrade) {
                        $errorstr = 'lessthanmin';
                    } else if ($bounded < $finalgrade) {
                        $errorstr = 'morethanmax';
                    }
                }

                if ($errorstr) {
                    $user = $DB->get_record('user', array('id' => $userid), 'id, ' . get_all_user_name_fields(true));
                    $gradestr = new stdClass();
                    $gradestr->username = fullname($user);
                    $gradestr->itemname = $grade_item->get_name();
                    $json_object->message = get_string($errorstr, 'grades', $gradestr);
                    $json_object->result = "error";

                }

                $finalvalue = $finalgrade;

            } else if ($type == 'feedback') {
                $finalgrade = false;
                $trimmed = trim($newvalue);
                if (empty($trimmed)) {
                    $feedback = NULL;
                } else {
                    $feedback = $newvalue;
                }

                $finalvalue = $feedback;
            }

            if (!empty($json_object->result) && $json_object->result == 'error') {
                echo json_encode($json_object);
                die();
            } else {
                $json_object->gradevalue = $finalvalue;

                if ($grade_item->update_final_grade($userid, $finalgrade, 'gradebook', $feedback, FORMAT_MOODLE)) {
                    $json_object->result = 'success';
                    $json_object->message = false;
                } else {
                    $json_object->result = 'error';
                    $json_object->message = "TO BE LOCALISED: Failure to update final grade!";
                    echo json_encode($json_object);
                    die();
                }

                                $sql = "SELECT gg.id, gi.id AS itemid, gi.scaleid AS scale, gg.userid AS userid, finalgrade, gg.overridden AS overridden "
                     . "FROM {grade_grades} gg, {grade_items} gi WHERE "
                     . "gi.courseid = ? AND gg.itemid = gi.id AND gg.userid = ?";
                $records = $DB->get_records_sql($sql, array($courseid, $userid));
                $json_object->row = $records;
                echo json_encode($json_object);
                die();
            }
        } else {
            $json_object = new stdClass();
            $json_object->result = "error";
            $json_object->message = "Missing parameter to ajax UPDATE callback: \n" .
                                    "  userid: $userid,\n  itemid: $itemid\n,  type: $type\n,  newvalue: $newvalue";
            echo json_encode($json_object);
        }

        break;
}


