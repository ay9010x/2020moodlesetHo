<?php



define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/clilib.php');

set_debugging(DEBUG_DEVELOPER, true);

if (!is_enabled_auth('ldap')) {
    error_log('[AUTH LDAP] '.get_string('pluginnotenabled', 'auth_ldap'));
    die;
}

cli_problem('[AUTH LDAP] The users sync cron has been deprecated. Please use the scheduled task instead.');

$taskdisabled = \core\task\manager::get_scheduled_task('auth_ldap\task\sync_task');
if (!$taskdisabled->get_disabled()) {
    cli_error('[AUTH LDAP] The scheduled task sync_task is enabled, the cron execution has been aborted.');
}

$ldapauth = get_auth_plugin('ldap');
$ldapauth->sync_users(true);

