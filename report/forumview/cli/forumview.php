<?php


define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      require_once($CFG->libdir.'/cronlib.php');
require('../locallib.php');

list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute Study Track.

This script executes user study tracking and is designed to be called via cron.

Options:
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php report/forumview/cli/forumview.php
";

    echo $help;
    die;
}
if (CLI_MAINTENANCE) {
    echo "CLI maintenance mode active, backup execution suspended.\n";
    exit(1);
}

if (moodle_needs_upgrading()) {
    echo "Moodle upgrade pending, backup execution suspended.\n";
    exit(1);
}

require_once($CFG->libdir.'/adminlib.php');

if (!empty($CFG->showcronsql)) {
    $DB->set_debug(true);
}
if (!empty($CFG->showcrondebugging)) {
    $CFG->debug = DEBUG_DEVELOPER;
    $CFG->debugdisplay = true;
}

$starttime = microtime();

cron_setup_user();

$timenow = time();

mtrace("Server Time: ".date('r',$timenow)."\n\n");
$track = new report_forumview_track();
$track->report_forumview_analysis();

mtrace("Study track completed correctly");

$difftime = microtime_diff($starttime, microtime());
mtrace("Execution took ".$difftime." seconds");