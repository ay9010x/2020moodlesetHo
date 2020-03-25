<?php



define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once("$CFG->dirroot/enrol/meta/locallib.php");

list($options, $unrecognized) = cli_get_params(array('verbose'=>false, 'help'=>false), array('v'=>'verbose', 'h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Execute meta course enrol sync.

Options:
-v, --verbose         Print verbose progess information
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php enrol/meta/cli/sync.php
";

    echo $help;
    die;
}

$verbose = !empty($options['verbose']);

$result = enrol_meta_sync(null, $verbose);

exit($result);