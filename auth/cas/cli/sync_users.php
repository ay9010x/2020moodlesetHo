<?php



define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/clilib.php');

set_debugging(DEBUG_DEVELOPER, true);

if (!is_enabled_auth('cas')) {
    error_log('[AUTH CAS] '.get_string('pluginnotenabled', 'auth_ldap'));
    die;
}

cli_problem('[AUTH CAS] The sync users cron has been deprecated. Please use the scheduled task instead.');

$task = \core\task\manager::get_scheduled_task('auth_cas\task\sync_task');
if (!$task->get_disabled()) {
    cli_error('[AUTH CAS] The scheduled task sync_task is enabled, the cron execution has been aborted.');
}

$casauth = get_auth_plugin('cas');
$casauth->sync_users(true);

