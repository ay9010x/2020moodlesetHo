<?php



require(__DIR__ . '/../../../../../config.php');
require_once($CFG->dirroot . '/lib/editor/atto/lib.php');

defined('BEHAT_SITE_RUNNING') || die('Only available on Behat test server');

$PAGE->set_url('/lib/editor/atto/tests/fixtures/override_plugins_example.php');
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();

$normal = optional_param('normaleditor', '', PARAM_RAW);
$special = optional_param('specialeditor', '', PARAM_RAW);
if ($normal !== '' || $special !== '') {
    echo html_writer::start_div('normalresult');
    echo s($normal);
    echo html_writer::end_div();
    echo html_writer::start_div('specialresult');
    echo s($special);
    echo html_writer::end_div();
} else {
        echo html_writer::start_tag('form', array('method' => 'post', 'action' => 'custom_toolbar_example.php'));
    echo html_writer::start_div();

        $options = array();
    $atto = new atto_texteditor();

        echo html_writer::start_div('normaldiv');
    echo $OUTPUT->heading('Normal Atto');
    echo html_writer::div(html_writer::tag('textarea', '',
            array('id' => 'normaleditor', 'name' => 'normaleditor', 'rows' => 10)));
    $atto->use_editor('normaleditor', $options);
    echo html_writer::end_div();

        echo html_writer::start_div('specialdiv');
    $options['atto:toolbar'] = <<<EOT
style1 = bold, italic
list = unorderedlist, orderedlist
EOT;
    echo $OUTPUT->heading('Special Atto');
    echo html_writer::div(html_writer::tag('textarea', '',
            array('id' => 'specialeditor', 'name' => 'specialeditor', 'rows' => 10)));
    $atto->use_editor('specialeditor', $options);
    echo html_writer::end_div();

        echo html_writer::start_div('', array('style' => 'margin-top: 20px'));
    echo html_writer::tag('button', 'Submit and see the HTML');
    echo html_writer::end_div();

    echo html_writer::end_div();
    echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();
