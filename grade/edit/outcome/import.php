<?php



require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once('import_outcomes_form.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$action   = optional_param('action', '', PARAM_ALPHA);
$scope    = optional_param('scope', 'custom', PARAM_ALPHA);

$PAGE->set_url('/grade/edit/outcome/import.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('admin');

if ($courseid) {
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('nocourseid');
    }
    require_login($course);
    $context = context_course::instance($course->id);

    if (empty($CFG->enableoutcomes)) {
        redirect('../../index.php?id='.$courseid);
    }

} else {
    require_once $CFG->libdir.'/adminlib.php';
    admin_externalpage_setup('outcomes');
    $context = context_system::instance();
}

require_capability('moodle/grade:manageoutcomes', $context);

$navigation = grade_build_nav(__FILE__, get_string('outcomes', 'grades'), $courseid);

$upload_form = new import_outcomes_form();

if (!$upload_form->get_data()) {
    print_grade_page_head($courseid, 'outcome', 'import', get_string('importoutcomes', 'grades'));
    $upload_form->display();
    echo $OUTPUT->footer();
    die;
}
print_grade_page_head($courseid, 'outcome', 'import', get_string('importoutcomes', 'grades'));

$imported_file = $CFG->tempdir . '/outcomeimport/importedfile_'.time().'.csv';
make_temp_directory('outcomeimport');

if (!$upload_form->save_file('userfile', $imported_file, true)) {
    redirect('import.php'. ($courseid ? "?courseid=$courseid" : ''), get_string('importfilemissing', 'grades'));
}

if (isset($courseid) && ($scope  == 'custom')) {
        $local_scope = true;
} elseif (($scope == 'global') && has_capability('moodle/grade:manage', context_system::instance())) {
        $local_scope = false;
} else {
        redirect('index.php', get_string('importerror', 'grades'));
}

if ($handle = fopen($imported_file, 'r')) {
    $line = 0;     $file_headers = '';

        $headers = array('outcome_name' => 0, 'outcome_shortname' => 1, 'scale_name' => 3, 'scale_items' => 4);
    $optional_headers = array('outcome_description'=>2, 'scale_description' => 5);
    $imported_headers = array(); 
    $fatal_error = false;

            while ( $csv_data = fgetcsv($handle, 8192, ';', '"')) {         $line++;

                if ($csv_data == array(null)) {
            continue;
        }

                if ($file_headers == '') {

            $file_headers = array_flip($csv_data); 
            $error = false;
            foreach($headers as $key => $value) {
                                if (!array_key_exists($key, $file_headers)) {
                    $error = true;
                    break;
                }
            }
            if ($error) {
                echo $OUTPUT->box_start('generalbox importoutcomenofile buttons');
                echo get_string('importoutcomenofile', 'grades', $line);
                echo $OUTPUT->single_button(new moodle_url('/grade/edit/outcome/import.php', array('courseid'=> $courseid)), get_string('back'), 'get');
                echo $OUTPUT->box_end();
                $fatal_error = true;
                break;
            }

            foreach(array_merge($headers, $optional_headers) as $header => $position) {
                                $imported_headers[$header] = $file_headers[$header];
            }

            continue;         }

                        if ( count($csv_data) != count($file_headers) ) {
            echo $OUTPUT->box_start('generalbox importoutcomenofile');
            echo get_string('importoutcomenofile', 'grades', $line);
            echo $OUTPUT->single_button(new moodle_url('/grade/edit/outcome/import.php', array('courseid'=> $courseid)), get_string('back'), 'get');
            echo $OUTPUT->box_end();
            $fatal_error = true;
                        break;
        }

                foreach ($headers as $header => $position) {
            if ($csv_data[$imported_headers[$header]] == '') {
                echo $OUTPUT->box_start('generalbox importoutcomenofile');
                echo get_string('importoutcomenofile', 'grades', $line);
                echo $OUTPUT->single_button(new moodle_url('/grade/edit/outcome/import.php', array('courseid'=> $courseid)), get_string('back'), 'get');
                echo $OUTPUT->box_end();
                $fatal_error = true;
                break;
            }
        }

                if ($fatal_error) {
            break;
        }
        $params = array($csv_data[$imported_headers['outcome_shortname']]);
        $wheresql = 'shortname = ? ';

        if ($local_scope) {
            $params[] = $courseid;
            $wheresql .= ' AND courseid = ?';
        } else {
            $wheresql .= ' AND courseid IS NULL';
        }

        $outcome = $DB->get_records_select('grade_outcomes', $wheresql, $params);

        if ($outcome) {
                        echo $OUTPUT->box(get_string('importskippedoutcome', 'grades', $csv_data[$imported_headers['outcome_shortname']]));
            continue;
        }

                $params = array($csv_data[$imported_headers['scale_name']], $csv_data[$imported_headers['scale_items']], $courseid);
        $wheresql = 'name = ? AND scale = ? AND (courseid = ? OR courseid = 0)';
        $scale = $DB->get_records_select('scale', $wheresql, $params);

        if ($scale) {
                        $scale_id = key($scale);
        } else {
            if (!has_capability('moodle/course:managescales', $context)) {
                echo $OUTPUT->box(get_string('importskippednomanagescale', 'grades', $csv_data[$imported_headers['outcome_shortname']]));
                continue;
            } else {
                                $scale_data = array('name' => $csv_data[$imported_headers['scale_name']],
                        'scale' => $csv_data[$imported_headers['scale_items']],
                        'description' => $csv_data[$imported_headers['scale_description']],
                        'userid' => $USER->id);

                if ($local_scope) {
                    $scale_data['courseid'] = $courseid;
                } else {
                    $scale_data['courseid'] = 0;                 }
                $scale = new grade_scale($scale_data);
                $scale_id = $scale->insert();
            }
        }

                $outcome_data = array('shortname' => $csv_data[$imported_headers['outcome_shortname']],
                'fullname' => $csv_data[$imported_headers['outcome_name']],
                'scaleid' => $scale_id,
                'description' => $csv_data[$imported_headers['outcome_description']],
                'usermodified' => $USER->id);

        if ($local_scope) {
            $outcome_data['courseid'] = $courseid;
        } else {
            $outcome_data['courseid'] = null;         }
        $outcome = new grade_outcome($outcome_data);
        $outcome_id = $outcome->insert();

        $outcome_success_strings = new StdClass();
        $outcome_success_strings->name = $outcome_data['fullname'];
        $outcome_success_strings->id = $outcome_id;
        echo $OUTPUT->box(get_string('importoutcomesuccess', 'grades', $outcome_success_strings));
    }
} else {
    echo $OUTPUT->box(get_string('importoutcomenofile', 'grades', 0));
}

fclose($handle);
unlink($imported_file);

echo $OUTPUT->footer();
