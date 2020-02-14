<?php



define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');


list($options, $unrecognized) = cli_get_params(array('help'=>false),
    array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Fix incorrectly deleted users.

        This scripts detects users that are marked as deleted instead
        of calling delete_user().

        Deleted users do not have original username, idnumber or email,
        we must also delete all roles, enrolments, group memberships, etc.

        Please note this script does not delete any public information
        such as forum posts.

        Options:
        -h, --help            Print out this help

        Example:
        \$sudo -u www-data /usr/bin/php admin/cli/fix_deleted_users.php
        ";

    echo $help;
    die;
}

cli_heading('Looking for sloppy user deletes');

$sql = "SELECT *
          FROM {user}
         WHERE deleted = 1 AND email LIKE '%@%' AND username NOT LIKE '%@%'";
$rs = $DB->get_recordset_sql($sql);
foreach ($rs as $user) {
    echo "Redeleting user $user->id: $user->username ($user->email)\n";
    delete_user($user);
}

cli_heading('Deleting all leftovers');

$DB->set_field('user', 'idnumber', '', array('deleted'=>1));

$DB->delete_records_select('role_assignments', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('cohort_members', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('groups_members', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('user_enrolments', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('user_preferences', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('user_info_data', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('user_lastaccess', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('external_tokens', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
$DB->delete_records_select('external_services_users', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");

exit(0);
