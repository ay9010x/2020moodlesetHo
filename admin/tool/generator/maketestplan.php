<?php



require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('toolgeneratortestplan');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('maketestplan', 'tool_generator'));

$context = context_system::instance();
$markdownlink = '[' . tool_generator_testplan_backend::get_repourl() . '](' . tool_generator_testplan_backend::get_repourl() . ')';
echo $OUTPUT->box(format_text(get_string('testplanexplanation', 'tool_generator', $markdownlink),
        FORMAT_MARKDOWN, array('context' => $context)));

if (!$CFG->debugdeveloper) {
    echo $OUTPUT->notification(get_string('error_notdebugging', 'tool_generator'));
    echo $OUTPUT->footer();
    exit;
}

$mform = new tool_generator_make_testplan_form('maketestplan.php');
if ($data = $mform->get_data()) {

        $testplanfile = tool_generator_testplan_backend::create_testplan_file($data->courseid, $data->size);
    $usersfile = tool_generator_testplan_backend::create_users_file($data->courseid, $data->updateuserspassword);

        $testplanurl = moodle_url::make_pluginfile_url(
        $testplanfile->get_contextid(),
        $testplanfile->get_component(),
        $testplanfile->get_filearea(),
        $testplanfile->get_itemid(),
        $testplanfile->get_filepath(),
        $testplanfile->get_filename()
    );
    echo html_writer::div(
        html_writer::link($testplanurl, get_string('downloadtestplan', 'tool_generator'))
    );

        $usersfileurl = moodle_url::make_pluginfile_url(
        $usersfile->get_contextid(),
        $usersfile->get_component(),
        $usersfile->get_filearea(),
        $usersfile->get_itemid(),
        $usersfile->get_filepath(),
        $usersfile->get_filename()
    );
    echo html_writer::div(
        html_writer::link($usersfileurl, get_string('downloadusersfile', 'tool_generator'))
    );

} else {
        $mform->display();
}

echo $OUTPUT->footer();
