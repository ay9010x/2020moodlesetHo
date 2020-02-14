<?php


require_once '../../../config.php';
require_once 'lib.php';
require_once $CFG->libdir.'/filelib.php';

$gradesurl = required_param('url', PARAM_URL); $id        = required_param('id', PARAM_INT); $feedback  = optional_param('feedback', 0, PARAM_BOOL);

$url = new moodle_url('/grade/import/xml/import.php', array('id' => $id,'url' => $gradesurl));
if ($feedback !== 0) {
    $url->param('feedback', $feedback);
}
$PAGE->set_url($url);

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($id);

require_capability('moodle/grade:import', $context);
require_capability('gradeimport/xml:view', $context);


core_php_time_limit::raise();
raise_memory_limit(MEMORY_EXTRA);

$text = download_file_content($gradesurl);
if ($text === false) {
    print_error('cannotreadfile', 'error',
            $CFG->wwwroot . '/grade/import/xml/index.php?id=' . $id, $gradesurl);
}

$error = '';
$importcode = import_xml_grades($text, $course, $error);

if ($importcode !== false) {
    
    if (defined('USER_KEY_LOGIN')) {
        if (grade_import_commit($id, $importcode, $feedback, false)) {
            echo 'ok';
            die;
        } else {
            print_error('cannotimportgrade');         }

    } else {
        print_grade_page_head($course->id, 'import', 'xml', get_string('importxml', 'grades'));

        grade_import_commit($id, $importcode, $feedback, true);

        echo $OUTPUT->footer();
        die;
    }

} else {
    print_error('errorduringimport', 'gradeimport_xml',
            $CFG->wwwroot . '/grade/import/xml/index.php?id=' . $id, $error);
}
