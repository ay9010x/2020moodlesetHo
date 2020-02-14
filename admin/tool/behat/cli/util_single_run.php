<?php




if (isset($_SERVER['REMOTE_ADDR'])) {
    die(); }

require_once(__DIR__ . '/../../../../lib/clilib.php');
require_once(__DIR__ . '/../../../../lib/behat/lib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'help'        => false,
        'install'     => false,
        'parallel'    => 0,
        'run'         => '',
        'drop'        => false,
        'enable'      => false,
        'disable'     => false,
        'diag'        => false,
        'tags'        => '',
        'updatesteps' => false,
    ),
    array(
        'h' => 'help'
    )
);

if ($options['install'] or $options['drop']) {
    define('CACHE_DISABLE_ALL', true);
}

$help = "
Behat utilities to manage the test environment

Usage:
  php util_single_run.php [--install|--drop|--enable|--disable|--diag|--updatesteps|--help]

Options:
--install     Installs the test environment for acceptance tests
--drop        Drops the database tables and the dataroot contents
--enable      Enables test environment and updates tests list
--disable     Disables test environment
--diag        Get behat test environment status code
--updatesteps Update feature step file.

-h, --help Print out this help

Example from Moodle root directory:
\$ php admin/tool/behat/cli/util_single_run.php --enable

More info in http://docs.moodle.org/dev/Acceptance_testing#Running_tests
";

if (!empty($options['help'])) {
    echo $help;
    exit(0);
}

define('BEHAT_UTIL', true);
define('CLI_SCRIPT', true);
define('NO_OUTPUT_BUFFERING', true);
define('IGNORE_COMPONENT_CACHE', true);

if ($options['run']) {
    define('BEHAT_CURRENT_RUN', $options['run']);
}

define('ABORT_AFTER_CONFIG', true);
require_once(__DIR__ . '/../../../../config.php');

$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
error_reporting($CFG->debug);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

define('ABORT_AFTER_CONFIG_CANCEL', true);
require("$CFG->dirroot/lib/setup.php");

raise_memory_limit(MEMORY_HUGE);

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/upgradelib.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/installlib.php');
require_once($CFG->libdir.'/testing/classes/test_lock.php');

if ($unrecognized) {
    $unrecognized = implode(PHP_EOL . "  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

require_once($CFG->libdir . '/behat/classes/util.php');
require_once($CFG->libdir . '/behat/classes/behat_command.php');
require_once($CFG->libdir . '/behat/classes/behat_config_manager.php');

if ($options['run']) {
    if (!$options['parallel']) {
        $options['parallel'] = behat_config_manager::get_parallel_test_runs();
    }
    if (empty($options['parallel']) || $options['run'] > $options['parallel']) {
        echo "Parallel runs can't be more then ".$options['parallel'].PHP_EOL;
        exit(1);
    }
    $CFG->behatrunprocess = $options['run'];
}

if ($options['install']) {
    behat_util::install_site();

        if (empty($options['run'])) {
        mtrace("Acceptance tests site installed");
    }

} else if ($options['drop']) {
        test_lock::acquire('behat');
    behat_util::drop_site();
        if (empty($options['run'])) {
        mtrace("Acceptance tests site dropped");
    }

} else if ($options['enable']) {
    if (!empty($options['parallel'])) {
                $filepath = behat_config_manager::get_parallel_test_file_path();
        if (!file_put_contents($filepath, $options['parallel'])) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, 'File ' . $filepath . ' can not be created');
        }
    }

        behat_util::start_test_mode();

        if (empty($options['run'])) {
                if (behat_config_manager::$autoprofileconversion) {
            mtrace("2.5 behat profile detected, automatically converted to current 3.x format");
        }

        $runtestscommand = behat_command::get_behat_command(true, !empty($options['run']));

        $runtestscommand .= ' --config ' . behat_config_manager::get_behat_cli_config_filepath();
        mtrace("Acceptance tests environment enabled on $CFG->behat_wwwroot, to run the tests use: " . PHP_EOL .
            $runtestscommand);
    }

} else if ($options['disable']) {
    behat_util::stop_test_mode();
        if (empty($options['run'])) {
        mtrace("Acceptance tests environment disabled");
    }

} else if ($options['diag']) {
    $code = behat_util::get_behat_status();
    exit($code);

} else if ($options['updatesteps']) {
    if (defined('BEHAT_FEATURE_STEP_FILE') && BEHAT_FEATURE_STEP_FILE) {
        $behatstepfile = BEHAT_FEATURE_STEP_FILE;
    } else {
        echo "BEHAT_FEATURE_STEP_FILE is not set, please ensure you set this to writable file" . PHP_EOL;
        exit(1);
    }

        $featurestepscmd = behat_command::get_behat_command(true);
    $featurestepscmd .= ' --config ' . behat_config_manager::get_behat_cli_config_filepath();
    $featurestepscmd .= ' --dry-run --format=moodle_step_count';
    $processes = cli_execute_parallel(array($featurestepscmd), __DIR__ . "/../../../../");
    $status = print_update_step_output(array_pop($processes), $behatstepfile);

    exit($status);
} else {
    echo $help;
    exit(1);
}

exit(0);


function print_update_step_output($process, $featurestepfile) {
    $printedlength = 0;

    echo "Updating steps feature file for parallel behat runs" . PHP_EOL;

        while ($process->isRunning()) {
        usleep(10000);
        $op = $process->getIncrementalOutput();
        if (trim($op)) {
            echo ".";
            $printedlength++;
            if ($printedlength > 70) {
                $printedlength = 0;
                echo PHP_EOL;
            }
        }
    }

        $exitcode = $process->getExitCode();
        if ($exitcode != 0) {
        echo $process->getErrorOutput();
        exit($exitcode);
    }

        $featuresteps = $process->getOutput();
    $featuresteps = explode(PHP_EOL, $featuresteps);

    $realroot = realpath(__DIR__.'/../../../../').'/';
    foreach ($featuresteps as $featurestep) {
        if (trim($featurestep)) {
            $step = explode("::", $featurestep);
            $step[0] = str_replace($realroot, '', $step[0]);
            $steps[$step[0]] = $step[1];
        }
    }

    if ($existing = @json_decode(file_get_contents($featurestepfile), true)) {
        $steps = array_merge($existing, $steps);
    }
    arsort($steps);

    if (!@file_put_contents($featurestepfile, json_encode($steps, JSON_PRETTY_PRINT))) {
        behat_error(BEHAT_EXITCODE_PERMISSIONS, 'File ' . $featurestepfile . ' can not be created');
        $exitcode = -1;
    }

    echo PHP_EOL. "Updated step count in " . $featurestepfile . PHP_EOL;

    return $exitcode;
}