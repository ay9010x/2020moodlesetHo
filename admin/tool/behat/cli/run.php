<?php



if (isset($_SERVER['REMOTE_ADDR'])) {
    die(); }

define('CLI_SCRIPT', true);
define('ABORT_AFTER_CONFIG', true);
define('CACHE_DISABLE_ALL', true);
define('NO_OUTPUT_BUFFERING', true);

require_once(__DIR__ .'/../../../../config.php');
require_once(__DIR__.'/../../../../lib/clilib.php');
require_once(__DIR__.'/../../../../lib/behat/lib.php');
require_once(__DIR__.'/../../../../lib/behat/classes/behat_command.php');
require_once(__DIR__.'/../../../../lib/behat/classes/behat_config_manager.php');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

list($options, $unrecognised) = cli_get_params(
    array(
        'stop-on-failure' => 0,
        'verbose'  => false,
        'replace'  => false,
        'help'     => false,
        'tags'     => '',
        'profile'  => '',
        'feature'  => '',
        'fromrun'  => 1,
        'torun'    => 0,
        'single-run' => false,
    ),
    array(
        'h' => 'help',
        't' => 'tags',
        'p' => 'profile',
        's' => 'single-run',
    )
);

$help = "
Behat utilities to run behat tests in parallel

Usage:
  php run.php [--BEHAT_OPTION=\"value\"] [--feature=\"value\"] [--replace] [--fromrun=value --torun=value] [--help]

Options:
--BEHAT_OPTION     Any combination of behat option specified in http://behat.readthedocs.org/en/v2.5/guides/6.cli.html
--feature          Only execute specified feature file (Absolute path of feature file).
--replace          Replace args string with run process number, useful for output.
--fromrun          Execute run starting from (Used for parallel runs on different vms)
--torun            Execute run till (Used for parallel runs on different vms)

-h, --help         Print out this help

Example from Moodle root directory:
\$ php admin/tool/behat/cli/run.php --tags=\"@javascript\"

More info in http://docs.moodle.org/dev/Acceptance_testing#Running_tests
";

if (!empty($options['help'])) {
    echo $help;
    exit(0);
}

$parallelrun = behat_config_manager::get_parallel_test_runs($options['fromrun']);

if (empty($options['torun'])) {
    $options['torun'] = $parallelrun;
}

if (extension_loaded('pcntl')) {
    $disabled = explode(',', ini_get('disable_functions'));
    if (!in_array('pcntl_signal', $disabled)) {
                declare(ticks = 1);

        pcntl_signal(SIGTERM, "signal_handler");
        pcntl_signal(SIGINT, "signal_handler");
    }
}

$time = microtime(true);
array_walk($unrecognised, function (&$v) {
    if ($x = preg_filter("#^(-+\w+)=(.+)#", "\$1=\"\$2\"", $v)) {
        $v = $x;
    } else if (!preg_match("#^-#", $v)) {
        $v = escapeshellarg($v);
    }
});
$extraopts = $unrecognised;

$tags = '';

if ($options['profile']) {
    $profile = $options['profile'];

        if (!isset($CFG->behat_config[$profile]) && !isset($CFG->behat_profiles[$profile]) &&
        !(isset($options['replace']) && (strpos($options['profile'], $options['replace']) >= 0 ))) {
        echo "Invalid profile passed: " . $profile . PHP_EOL;
        exit(1);
    }

    $extraopts[] = '--profile="' . $profile . '"';
        if (!empty($CFG->behat_config[$profile]['filters']['tags'])) {
        $tags = $CFG->behat_config[$profile]['filters']['tags'];
    }
}

if ($options['tags']) {
    $tags = $options['tags'];
    $extraopts[] = '--tags="' . $tags . '"';
}

if ($options['feature']) {
    $extraopts[] = $options['feature'];
            $options['torun'] = $options['fromrun'];
}

$extraopts = implode(' ', $extraopts);

if (empty($parallelrun)) {
    $cwd = getcwd();
    chdir(__DIR__);
    $runtestscommand = behat_command::get_behat_command(false, false, true);
    $runtestscommand .= ' --config ' . behat_config_manager::get_behat_cli_config_filepath();
    $runtestscommand .= ' ' . $extraopts;
    echo "Running single behat site:" . PHP_EOL;
    passthru("php $runtestscommand", $code);
    chdir($cwd);
    exit($code);
}

if ($tags) {
        $behatdataroot = $CFG->behat_dataroot;
    $behatwwwroot  = $CFG->behat_wwwroot;
    for ($i = 1; $i <= $parallelrun; $i++) {
        $CFG->behatrunprocess = $i;

        if (!empty($CFG->behat_parallel_run[$i - 1]['behat_wwwroot'])) {
            $CFG->behat_wwwroot = $CFG->behat_parallel_run[$i - 1]['behat_wwwroot'];
        } else {
            $CFG->behat_wwwroot = $behatwwwroot . "/" . BEHAT_PARALLEL_SITE_NAME . $i;
        }
        if (!empty($CFG->behat_parallel_run[$i - 1]['behat_dataroot'])) {
            $CFG->behat_dataroot = $CFG->behat_parallel_run[$i - 1]['behat_dataroot'];
        } else {
            $CFG->behat_dataroot = $behatdataroot . $i;
        }
        behat_config_manager::update_config_file('', true, $tags);
    }
    $CFG->behat_dataroot = $behatdataroot;
    $CFG->behat_wwwroot = $behatwwwroot;
    unset($CFG->behatrunprocess);
}

$cmds = array();
echo "Running " . ($options['torun'] - $options['fromrun'] + 1) . " parallel behat sites:" . PHP_EOL;

for ($i = $options['fromrun']; $i <= $options['torun']; $i++) {
    $CFG->behatrunprocess = $i;

        $myopts = !empty($options['replace']) ? str_replace($options['replace'], $i, $extraopts) : $extraopts;

    $behatcommand = behat_command::get_behat_command(false, false, true);
    $behatconfigpath = behat_config_manager::get_behat_cli_config_filepath($i);

        $cmds[BEHAT_PARALLEL_SITE_NAME . $i] = $behatcommand . ' --config ' . $behatconfigpath . " " . $myopts;
    echo "[" . BEHAT_PARALLEL_SITE_NAME . $i . "] " . $cmds[BEHAT_PARALLEL_SITE_NAME . $i] . PHP_EOL;
}

if (empty($cmds)) {
    echo "No commands to execute " . PHP_EOL;
    exit(1);
}

if (!behat_config_manager::create_parallel_site_links($options['fromrun'], $options['torun'])) {
    echo "Check permissions. If on windows, make sure you are running this command as admin" . PHP_EOL;
    exit(1);
}

$processes = cli_execute_parallel($cmds, __DIR__);
$stoponfail = empty($options['stop-on-failure']) ? false : true;

print_process_start_info($processes);

$exitcodes = print_combined_run_output($processes, $stoponfail);
$time = round(microtime(true) - $time, 1);
echo "Finished in " . gmdate("G\h i\m s\s", $time) . PHP_EOL . PHP_EOL;

ksort($exitcodes);

$status = 0;
$processcounter = 0;
foreach ($exitcodes as $exitcode) {
    if ($exitcode) {
        $status |= (1 << $processcounter);
    }
    $processcounter++;
}

$verbose = empty($options['verbose']) ? false : true;
$verbose = $verbose || !empty($status);

if ($verbose) {
        echo "Exit codes for each behat run: " . PHP_EOL;
    foreach ($exitcodes as $run => $exitcode) {
        echo $run . ": " . $exitcode . PHP_EOL;
    }

        if ($status) {
        echo "To re-run failed processes, you can use following commands:" . PHP_EOL;
        foreach ($cmds as $name => $cmd) {
            if (!empty($exitcodes[$name])) {
                echo "[" . $name . "] " . $cmd . PHP_EOL;
            }
        }
    }
    echo PHP_EOL;
}

print_each_process_info($processes, $verbose);

behat_config_manager::drop_parallel_site_links();

exit($status);


function signal_handler($signal) {
    switch ($signal) {
        case SIGTERM:
        case SIGKILL:
        case SIGINT:
                        behat_config_manager::drop_parallel_site_links();
            exit(1);
    }
}


function print_process_start_info($processes) {
    $printed = false;
        while (!$printed) {
        usleep(10000);
        foreach ($processes as $name => $process) {
                        if (!$process->isRunning()) {
                $printed = true;
                break;
            }

            $op = explode(PHP_EOL, $process->getOutput());
            if (count($op) >= 3) {
                foreach ($op as $line) {
                    if (trim($line) && (strpos($line, '.') !== 0)) {
                        echo $line . PHP_EOL;
                    }
                }
                $printed = true;
            }
        }
    }
}


function print_combined_run_output($processes, $stoponfail = false) {
    $exitcodes = array();
    $maxdotsonline = 70;
    $remainingprintlen = $maxdotsonline;
    $progresscount = 0;
    while (count($exitcodes) != count($processes)) {
        usleep(10000);
        foreach ($processes as $name => $process) {
            if ($process->isRunning()) {
                $op = $process->getIncrementalOutput();
                if (trim($op)) {
                    $update = preg_filter('#^\s*([FS\.\-]+)(?:\s+\d+)?\s*$#', '$1', $op);
                                        if ($stoponfail && (strpos($update, 'F') !== false)) {
                        $process->stop(0);
                    }

                    $strlentoprint = strlen($update);

                                        if ($strlentoprint < $remainingprintlen) {
                        echo $update;
                        $remainingprintlen = $remainingprintlen - $strlentoprint;
                    } else if ($strlentoprint == $remainingprintlen) {
                        $progresscount += $maxdotsonline;
                        echo $update ." " . $progresscount . PHP_EOL;
                        $remainingprintlen = $maxdotsonline;
                    } else {
                        while ($part = substr($update, 0, $remainingprintlen) > 0) {
                            $progresscount += $maxdotsonline;
                            echo $part . " " . $progresscount . PHP_EOL;
                            $update = substr($update, $remainingprintlen);
                            $remainingprintlen = $maxdotsonline;
                        }
                    }
                }
            } else {
                $exitcodes[$name] = $process->getExitCode();
                if ($stoponfail && ($exitcodes[$name] != 0)) {
                    foreach ($processes as $l => $p) {
                        $exitcodes[$l] = -1;
                        $process->stop(0);
                    }
                }
            }
        }
    }

    echo PHP_EOL;
    return $exitcodes;
}


function print_each_process_info($processes, $verbose = false) {
    foreach ($processes as $name => $process) {
        echo "**************** [" . $name . "] ****************" . PHP_EOL;
        if ($verbose) {
            echo $process->getOutput();
            echo $process->getErrorOutput();
        } else {
            $op = explode(PHP_EOL, $process->getOutput());
            foreach ($op as $line) {
                                if (trim($line) && (strpos($line, '.') !== 0) && (strpos($line, 'Moodle ') !== 0) &&
                    (strpos($line, 'Server OS ') !== 0) && (strpos($line, 'Started at ') !== 0) &&
                    (strpos($line, 'Browser specific fixes ') !== 0)) {
                    echo $line . PHP_EOL;
                }
            }
        }
        echo PHP_EOL;
    }
}
