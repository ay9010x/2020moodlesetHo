<?php



define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');

require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('toolgeneratorcourse');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('maketestcourse', 'tool_generator'));

$context = context_system::instance();
echo $OUTPUT->box(format_text(get_string('courseexplanation', 'tool_generator'),
        FORMAT_MARKDOWN, array('context' => $context)));

if (!debugging('', DEBUG_DEVELOPER)) {
    echo $OUTPUT->notification(get_string('error_notdebugging', 'tool_generator'));
    echo $OUTPUT->footer();
    exit;
}

$mform = new tool_generator_make_course_form('maketestcourse.php');
if ($data = $mform->get_data()) {
        echo $OUTPUT->heading(get_string('creating', 'tool_generator'));
    $backend = new tool_generator_course_backend(
        $data->shortname,
        $data->size,
        false,
        false,
        true,
        $data->fullname,
        $data->summary['text'],
        $data->summary['format']
    );
    $id = $backend->make();

    echo html_writer::div(
            html_writer::link(new moodle_url('/course/view.php', array('id' => $id)),
                get_string('continue')));
} else {
        $mform->display();
}

echo $OUTPUT->footer();
