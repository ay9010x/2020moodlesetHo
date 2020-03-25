<?php




define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      

list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Reset local user passwords, useful especially for admin acounts.

There are no security checks here because anybody who is able to
execute this file may execute any PHP too.

Options:
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/reset_password.php
";

    echo $help;
    die;
}
cli_heading('Password reset');
$prompt = "Enter username (manual authentication only)";
$username = cli_input($prompt);

if (!$user = $DB->get_record('user', array('auth'=>'manual', 'username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
    cli_error("Can not find user '$username'");
}

$prompt = "Enter new password";
$password = cli_input($prompt);

$errmsg = '';if (!check_password_policy($password, $errmsg)) {
    cli_error(html_to_text($errmsg, 0));
}

$hashedpassword = hash_internal_user_password($password);

$DB->set_field('user', 'password', $hashedpassword, array('id'=>$user->id));

echo "Password changed\n";

exit(0); 