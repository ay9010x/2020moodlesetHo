<?php



define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

$courseconfig = get_config('moodlecourse');

list($options, $unrecognized) = cli_get_params(array(
    'help' => false,
    'mode' => '',
    'updatemode' => 'nothing',
    'file' => '',
    'delimiter' => 'comma',
    'encoding' => 'UTF-8',
    'shortnametemplate' => '',
    'templatecourse' => false,
    'restorefile' => false,
    'allowdeletes' => false,
    'allowrenames' => false,
    'allowresets' => false,
    'reset' => false,
    'category' => coursecat::get_default()->id,
),
array(
    'h' => 'help',
    'm' => 'mode',
    'u' => 'updatemode',
    'f' => 'file',
    'd' => 'delimiter',
    'e' => 'encoding',
    't' => 'templatecourse',
    'r' => 'restorefile',
    'g' => 'format',
));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

$help =
"Execute Course Upload.

Options:
-h, --help                 Print out this help
-m, --mode                 Import mode: createnew, createall, createorupdate, update
-u, --updatemode           Update mode: nothing, dataonly, dataordefaults¸ missingonly
-f, --file                 CSV file
-d, --delimiter            CSV delimiter: colon, semicolon, tab, cfg, comma
-e, --encoding             CSV file encoding: utf8, ... etc
-t, --templatecourse       Shortname of the course to restore after import
-r, --restorefile          Backup file to restore after import
--reset                    Run the course reset after each course import
--allowdeletes             Allow courses to be deleted
--allowrenames             Allow courses to be renamed
--allowresets              Allow courses to be reset
--shortnametemplate        Template to generate the shortname from
--category                 ID of default category (--updatemode dataordefaults will use this value)


Example:
\$sudo -u www-data /usr/bin/php admin/tool/uploadcourse/cli/uploadcourse.php --mode=createnew \\
       --updatemode=dataonly --file=./courses.csv --delimiter=comma
";

if ($options['help']) {
    echo $help;
    die();
}
echo "Moodle course uploader running ...\n";

$processoroptions = array(
    'allowdeletes' => $options['allowdeletes'],
    'allowrenames' => $options['allowrenames'],
    'allowresets' => $options['allowresets'],
    'reset' => $options['reset'],
    'shortnametemplate' => $options['shortnametemplate']
);

$modes = array(
    'createnew' => tool_uploadcourse_processor::MODE_CREATE_NEW,
    'createall' => tool_uploadcourse_processor::MODE_CREATE_ALL,
    'createorupdate' => tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE,
    'update' => tool_uploadcourse_processor::MODE_UPDATE_ONLY
);
if (!isset($options['mode']) || !isset($modes[$options['mode']])) {
    echo get_string('invalidmode', 'tool_uploadcourse')."\n";
    echo $help;
    die();
}
$processoroptions['mode'] = $modes[$options['mode']];

$updatemodes = array(
    'nothing' => tool_uploadcourse_processor::UPDATE_NOTHING,
    'dataonly' => tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_ONLY,
    'dataordefaults' => tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_OR_DEFAUTLS,
    'missingonly' => tool_uploadcourse_processor::UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS
);
if (($processoroptions['mode'] === tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE ||
        $processoroptions['mode'] === tool_uploadcourse_processor::MODE_UPDATE_ONLY)
        && (!isset($options['updatemode']) || !isset($updatemodes[$options['updatemode']]))) {
    echo get_string('invalideupdatemode', 'tool_uploadcourse')."\n";
    echo $help;
    die();
}
$processoroptions['updatemode'] = $updatemodes[$options['updatemode']];

if (!empty($options['file'])) {
    $options['file'] = realpath($options['file']);
}
if (!file_exists($options['file'])) {
    echo get_string('invalidcsvfile', 'tool_uploadcourse')."\n";
    echo $help;
    die();
}

$encodings = core_text::get_encodings();
if (!isset($encodings[$options['encoding']])) {
    echo get_string('invalidencoding', 'tool_uploadcourse')."\n";
    echo $help;
    die();
}

$defaults = array();
$defaults['category'] = $options['category'];
$defaults['startdate'] = time() + 3600 * 24;
$defaults['newsitems'] = $courseconfig->newsitems;
$defaults['showgrades'] = $courseconfig->showgrades;
$defaults['showreports'] = $courseconfig->showreports;
$defaults['maxbytes'] = $courseconfig->maxbytes;
$defaults['legacyfiles'] = $CFG->legacyfilesinnewcourses;
$defaults['groupmode'] = $courseconfig->groupmode;
$defaults['groupmodeforce'] = $courseconfig->groupmodeforce;
$defaults['visible'] = $courseconfig->visible;
$defaults['lang'] =  $courseconfig->lang;

if (isset($options['templatecourse'])) {
    $processoroptions['templatecourse'] = $options['templatecourse'];
}

if ($options['restorefile']) {
    $options['restorefile'] = realpath($options['restorefile']);
}
if ($options['restorefile'] && !file_exists($options['restorefile'])) {
    echo get_string('invalidrestorefile', 'tool_uploadcourse')."\n";
    echo $help;
    die();
}
$processoroptions['restorefile'] = $options['restorefile'];

cron_setup_user();

$content = file_get_contents($options['file']);
$importid = csv_import_reader::get_new_iid('uploadcourse');
$cir = new csv_import_reader($importid, 'uploadcourse');
$readcount = $cir->load_csv_content($content, $options['encoding'], $options['delimiter']);
unset($content);
if ($readcount === false) {
    print_error('csvfileerror', 'tool_uploadcourse', '', $cir->get_error());
} else if ($readcount == 0) {
    print_error('csvemptyfile', 'error', '', $cir->get_error());
}
$processor = new tool_uploadcourse_processor($cir, $processoroptions, $defaults);
$processor->execute(new tool_uploadcourse_tracker(tool_uploadcourse_tracker::OUTPUT_PLAIN));
