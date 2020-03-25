<?php



define('NO_OUTPUT_BUFFERING', true);

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$testpath  = optional_param('testpath', '', PARAM_PATH);
$testclass = optional_param('testclass', '', PARAM_ALPHANUMEXT);
$execute   = optional_param('execute', 0, PARAM_BOOL);

navigation_node::override_active_url(new moodle_url('/admin/tool/phpunit/index.php'));
admin_externalpage_setup('toolphpunitwebrunner');

if (!$CFG->debugdeveloper) {
    print_error('notlocalisederrormessage', 'error', '', null, 'Not available on production sites, sorry.');
}

core_php_time_limit::raise(60*30);

$oldcwd = getcwd();
$code = 0;

if (!isset($CFG->phpunit_dataroot) or !isset($CFG->phpunit_prefix)) {
    tool_phpunit_problem('Missing $CFG->phpunit_dataroot or $CFG->phpunit_prefix, can not execute tests.');
}
if (!file_exists($CFG->phpunit_dataroot)) {
    mkdir($CFG->phpunit_dataroot, 02777, true);
}
if (!is_writable($CFG->phpunit_dataroot)) {
    tool_phpunit_problem('$CFG->phpunit_dataroot in not writable, can not execute tests.');
}
$output = null;
exec('php --version', $output, $code);
if ($code != 0) {
    tool_phpunit_problem('Can not execute \'php\' binary.');
}

if ($execute) {
    require_sesskey();

    chdir($CFG->dirroot);
    $output = null;
    exec("php $CFG->admin/tool/phpunit/cli/util.php --diag", $output, $code);
    if ($code == 0) {
        
    } else if ($code == PHPUNIT_EXITCODE_INSTALL) {
        tool_phpunit_header();
        echo $OUTPUT->box_start('generalbox');
        echo '<pre>';
        echo "Initialising test database:\n\n";
        chdir($CFG->dirroot);
        ignore_user_abort(true);
        passthru("php $CFG->admin/tool/phpunit/cli/util.php --buildconfig", $code);
        passthru("php $CFG->admin/tool/phpunit/cli/util.php --install", $code);
        chdir($oldcwd);
        echo '</pre>';
        echo $OUTPUT->box_end();
        if ($code != 0) {
            tool_phpunit_problem('Can not initialize database');
        }
        set_debugging(DEBUG_NONE, false);         redirect(new moodle_url($PAGE->url, array('execute'=>1, 'tespath'=>$testpath, 'testclass'=>$testclass, 'sesskey'=>sesskey())), 'Reloading page');
        echo $OUTPUT->footer();
        die();

    } else if ($code == PHPUNIT_EXITCODE_REINSTALL) {
        tool_phpunit_header();
        echo $OUTPUT->box_start('generalbox');
        echo '<pre>';
        echo "Reinitialising test database:\n\n";
        chdir($CFG->dirroot);
        ignore_user_abort(true);
        passthru("php $CFG->admin/tool/phpunit/cli/util.php --drop", $code);
        passthru("php $CFG->admin/tool/phpunit/cli/util.php --buildconfig", $code);
        passthru("php $CFG->admin/tool/phpunit/cli/util.php --install", $code);
        chdir($oldcwd);
        echo '</pre>';
        echo $OUTPUT->box_end();
        if ($code != 0) {
            tool_phpunit_problem('Can not initialize database');
        }
        set_debugging(DEBUG_NONE, false);         redirect(new moodle_url($PAGE->url, array('execute'=>1, 'tespath'=>$testpath, 'testclass'=>$testclass, 'sesskey'=>sesskey())), 'Reloading page');
        die();

    } else {
        tool_phpunit_header();
        echo $OUTPUT->box_start('generalbox');
        echo '<pre>';
        echo "Error: $code\n\n";
        echo implode("\n", $output);
        echo '</pre>';
        echo $OUTPUT->box_end();
        tool_phpunit_problem('Can not execute tests');
        die();
    }

    tool_phpunit_header();
    echo $OUTPUT->box_start('generalbox');
    echo '<pre>';

        $configdir = "$CFG->phpunit_dataroot/phpunit/webrunner.xml";
    if (!file_exists($configdir)) {
        passthru("php $CFG->admin/tool/phpunit/cli/util.php --buildconfig", $code);
        if ($code != 0) {
            tool_phpunit_problem('Can not create configuration file');
        }
    }
    $configdir = escapeshellarg($configdir);
            chdir($CFG->dirroot);
    passthru("php $CFG->admin/tool/phpunit/cli/util.php --run -c $configdir $testclass $testpath", $code);
    chdir($oldcwd);

    echo '</pre>';
    echo $OUTPUT->box_end();

} else {
    tool_phpunit_header();
}

echo $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter');
echo '<form method="get" action="webrunner.php">';
echo '<fieldset class="invisiblefieldset">';
echo '<label for="testpath">Test one file</label> ';
echo '<input type="text" id="testpath" name="testpath" value="'.s($testpath).'" size="50" /> (all test cases from webrunner.xml if empty)';
echo '</p>';
echo '<label for="testclass">Class name</label> ';
echo '<input type="text" id="testclass" name="testclass" value="'.s($testclass).'" size="50" /> (first class in file if empty)';
echo '</p>';
echo '<input type="submit" value="Run" />';
echo '<input type="hidden" name="execute" value="1" />';
echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
echo '</fieldset>';
echo '</form>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
die;





function tool_phpunit_header() {
    global $OUTPUT;
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'tool_phpunit'));
    echo $OUTPUT->box('EXPERIMENTAL: it is recommended to execute PHPUnit tests and init scripts only from command line.', array('generalbox'));
}


function tool_phpunit_problem($message) {
    global $PAGE;
    if (!$PAGE->headerprinted) {
        tool_phpunit_header();
    }
    notice($message, new moodle_url('/admin/tool/phpunit/'));
}
