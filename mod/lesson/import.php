<?php




require_once("../../config.php");
require_once($CFG->libdir.'/questionlib.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');
require_once($CFG->dirroot.'/mod/lesson/import_form.php');
require_once($CFG->dirroot.'/mod/lesson/format.php');  
$id     = required_param('id', PARAM_INT);         $pageid = optional_param('pageid', '', PARAM_INT); 
$PAGE->set_url('/mod/lesson/import.php', array('id'=>$id, 'pageid'=>$pageid));

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/lesson:edit', $context);

$strimportquestions = get_string("importquestions", "lesson");
$strlessons = get_string("modulenameplural", "lesson");

$manager = lesson_page_type_manager::get($lesson);

$data = new stdClass;
$data->id = $PAGE->cm->id;
$data->pageid = $pageid;

$mform = new lesson_import_form(null, array('formats'=>lesson_get_import_export_formats('import')));
$mform->set_data($data);

    $PAGE->navbar->add($strimportquestions);
    $PAGE->set_title($strimportquestions);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($lesson->name), 2);
    echo $OUTPUT->heading_with_help($strimportquestions, 'importquestions', 'lesson', '', '', 3);

if ($data = $mform->get_data()) {

    require_sesskey();

    $realfilename = $mform->get_new_filename('questionfile');
        $importfile = "{$CFG->tempdir}/questionimport/{$realfilename}";
    make_temp_directory('questionimport');
    if (!$result = $mform->save_file('questionfile', $importfile, true)) {
        throw new moodle_exception('uploadproblem');
    }

    $formatclass = 'qformat_'.$data->format;
    $formatclassfile = $CFG->dirroot.'/question/format/'.$data->format.'/format.php';
    if (!is_readable($formatclassfile)) {
        print_error('unknowformat','', '', $data->format);
            }
    require_once($formatclassfile);
    $format = new $formatclass();

    $format->set_importcontext($context);

        if (! $format->importpreprocess()) {
                print_error('preprocesserror', 'lesson');
            }

        if (! $format->importprocess($importfile, $lesson, $pageid)) {
                print_error('processerror', 'lesson');
            }

        if (! $format->importpostprocess()) {
                print_error('postprocesserror', 'lesson');
            }

            echo "<hr>";
    echo $OUTPUT->continue_button('view.php?id='.$PAGE->cm->id);

} else {

        $mform->display();
}

echo $OUTPUT->footer();
