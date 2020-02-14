<?php





if (defined('STDIN')) {
    fwrite(STDERR, "ERROR: This script no longer supports CLI, please use admin/cli/cron.php instead\n");
    exit(1);
}

define('CLI_SCRIPT', true);
define('WEB_CRON_EMULATED_CLI', 'defined'); define('NO_OUTPUT_BUFFERING', true);

require('../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/cronlib.php');

\core\session\manager::write_close();

if (!empty($CFG->cronclionly)) {
        print_error('cronerrorclionly', 'admin');
    exit;
}
if (!empty($CFG->cronremotepassword)) {
    $pass = optional_param('password', '', PARAM_RAW);
    if ($pass != $CFG->cronremotepassword) {
                print_error('cronerrorpassword', 'admin');
        exit;
    }
}

@header('Content-Type: text/plain; charset=utf-8');

@ini_set('html_errors', 'off');

cron_run();
