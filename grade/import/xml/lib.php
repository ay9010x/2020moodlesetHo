<?php


require_once $CFG->libdir.'/gradelib.php';
require_once($CFG->libdir.'/xmlize.php');
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/import/lib.php';

function import_xml_grades($text, $course, &$error) {
    global $USER, $DB;

    $importcode = get_new_importcode();

    $status = true;

    $content = xmlize($text);

    if (!empty($content['results']['#']['result'])) {
        $results = $content['results']['#']['result'];

        foreach ($results as $i => $result) {
            $gradeidnumber = $result['#']['assignment'][0]['#'];
            if (!$grade_items = grade_item::fetch_all(array('idnumber'=>$gradeidnumber, 'courseid'=>$course->id))) {
                                                $status = false;
                $error  = get_string('errincorrectgradeidnumber', 'gradeimport_xml', $gradeidnumber);
                break;
            } else if (count($grade_items) != 1) {
                $status = false;
                $error  = get_string('errduplicategradeidnumber', 'gradeimport_xml', $gradeidnumber);
                break;
            } else {
                $grade_item = reset($grade_items);
            }

                        if ($grade_item->is_locked()) {
                $status = false;
                $error  = get_string('gradeitemlocked', 'grades');
                break;
            }

                        $useridnumber = $result['#']['student'][0]['#'];
            if (!$user = $DB->get_record('user', array('idnumber' =>$useridnumber))) {
                                $status = false;
                $error = get_string('errincorrectuseridnumber', 'gradeimport_xml', $useridnumber);
                break;
            }

                        if ($grade_grade = new grade_grade(array('itemid'=>$grade_item->id, 'userid'=>$user->id))) {
                $grade_grade->grade_item =& $grade_item;
                if ($grade_grade->is_locked()) {
                                        $status = false;
                    $error  = get_string('gradelocked', 'grades');
                    break;
                }
            }

            $newgrade = new stdClass();
            $newgrade->itemid     = $grade_item->id;
            $newgrade->userid     = $user->id;
            $newgrade->importcode = $importcode;
            $newgrade->importer   = $USER->id;

                        if (isset($result['#']['score'][0]['#']) && $result['#']['score'][0]['#'] !== '-') {
                if (is_numeric($result['#']['score'][0]['#'])) {
                    $newgrade->finalgrade = $result['#']['score'][0]['#'];
                } else {
                    $status = false;
                    $error = get_string('badgrade', 'grades');
                    break;
                }
            } else {
                $newgrade->finalgrade = NULL;
            }

                        if (isset($result['#']['feedback'][0]['#'])) {
                $newgrade->feedback = $result['#']['feedback'][0]['#'];
            } else {
                $newgrade->feedback = NULL;
            }

                        $DB->insert_record('grade_import_values', $newgrade);
        }

    } else {
                        $status = false;
        $error = get_string('errbadxmlformat', 'gradeimport_xml');
    }

    if ($status) {
        return $importcode;

    } else {
        import_cleanup($importcode);
        return false;
    }
}

