<?php



define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once("$CFG->libdir/clilib.php");

set_debugging(DEBUG_DEVELOPER, true);

if (!enrol_is_enabled('ldap')) {
    cli_error(get_string('pluginnotenabled', 'enrol_ldap'), 2);
}


$enrol = enrol_get_plugin('ldap');

$trace = new text_progress_trace();

$enrol->sync_enrolments($trace);

exit(0);
