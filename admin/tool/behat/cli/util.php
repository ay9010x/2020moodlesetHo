<?php




if (isset($_SERVER['REMOTE_ADDR'])) {
    die(); }

define('BEHAT_UTIL', true);
define('CLI_SCRIPT', true);
define('NO_OUTPUT_BUFFERING', true);
define('IGNORE_COMPONENT_CACHE', true);
define('ABORT_AFTER_CONFIG', true);

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/../../../../lib/clilib.php');
require_once(__DIR__ . '/../../../../lib/behat/lib.php');
require_once(__DIR__ . '/../../../../lib/behat/classes/behat_command.php');
require_once(__DIR__ . '/../../../../lib/behat/classes/behat_config_manager.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'help'        => false,
        'install'     => false,
        'drop'        => false,
        'enable'      => false,
        'disable'     => false,
        'diag'        => false,
        'parallel'    => 0,
        'maxruns'     => false,
        'updatesteps' => false,
        'fromrun'     => 1,
        'torun'       => 0,
    ),
    array(
        'h' => 'help',
        'j' => 'parallel',
        'm' => 'maxruns'
    )
);

$help = "
Behat utilities to manage the test environment

Usage:
  php util.php [--install|--drop|--enable|--disable|--diag|--updatesteps|--help] [--parallel=value [--maxruns=value]]

Options:
--install      Installs the test environment for acceptance tests
--drop         Drops the database tables and the dataroot contents
--enable       Enables test environment and updates tests list
--disable      Disables test environment
--diag         Get behat test environment status code
--updatesteps  Update feature step file.
-j, --parallel Number of parallel behat run operation
-m, --maxruns  Max parallel processes to be executed at one time.

-h, --help     Print out this help

Example from Moodle root directory:
\$ php admin/tool/behat/cli/util.php --enable --parallel=4

More info in http://docs.moodle.org/dev/Acceptance_testing#Running_tests
";

if (!empty($options['help'])) {
    echo $help;
    exit(0);
}

$cwd = getcwd();

if ((empty($options['parallel'])) && ($options['drop']) || $options['updatesteps']) {
        $options['parallel'] = behat_config_manager::get_parallel_test_runs($options['fromrun']);
}

if (empty($options['parallel'])) {
    chdir(__DIR__);
        passthru("php util_single_run.php --diag", $status);
    if ($status) {
        exit ($status);
    }
    $cmd = commands_to_execute($options);
    $processes = cli_execute_parallel(array($cmd), __DIR__);
    $status = print_sequential_output($processes, false);
    chdir($cwd);
    exit($status);
}

if (empty($options['torun'])) {
    $options['torun'] = $options['parallel'];
}

$status = false;
$cmds = commands_to_execute($options);

if ($options['diag'] || $options['enable'] || $options['disable']) {
        foreach (array_chunk($cmds, 1, true) as $cmd) {
        $processes = cli_execute_parallel($cmd, __DIR__);
        print_sequential_output($processes);
    }

} else if ($options['drop']) {
    $processes = cli_execute_parallel($cmds, __DIR__);
    $exitcodes = print_combined_drop_output($processes);
    foreach ($exitcodes as $exitcode) {
        $status = (bool)$status || (bool)$exitcode;
    }

} else if ($options['install']) {
        if ($options['maxruns']) {
        foreach (array_chunk($cmds, $options['maxruns'], true) as $chunk) {
            $processes = cli_execute_parallel($chunk, __DIR__);
            $exitcodes = print_combined_install_output($processes);
            foreach ($exitcodes as $name => $exitcode) {
                if ($exitcode != 0) {
                    echo "Failed process [[$name]]" . PHP_EOL;
                    echo $processes[$name]->getOutput();
                    echo PHP_EOL;
                    echo $processes[$name]->getErrorOutput();
                    echo PHP_EOL . PHP_EOL;
                }
                $status = (bool)$status || (bool)$exitcode;
            }
        }
    } else {
        $processes = cli_execute_parallel($cmds, __DIR__);
        $exitcodes = print_combined_install_output($processes);
        foreach ($exitcodes as $name => $exitcode) {
            if ($exitcode != 0) {
                echo "Failed process [[$name]]" . PHP_EOL;
                echo $processes[$name]->getOutput();
                echo PHP_EOL;
                echo $processes[$name]->getErrorOutput();
                echo PHP_EOL . PHP_EOL;
            }
            $status = (bool)$status || (bool)$exitcode;
        }
    }

} else if ($options['updatesteps']) {
        if (empty($options['parallel'])) {
        behat_config_manager::update_config_file();
    } else {
                for ($i = $options['fromrun']; $i <= $options['torun']; $i++) {
            $CFG->behatrunprocess = $i;
            behat_config_manager::update_config_file();
        }
        unset($CFG->behatrunprocess);
    }

        foreach (array_chunk($cmds, 1, true) as $cmd) {
        $processes = cli_execute_parallel($cmd, __DIR__);
        print_sequential_output($processes);
    }
    exit(0);

} else {
        echo $help;
    exit(1);
}

if ($status) {
    echo "Unknown failure $status" . PHP_EOL;
    exit((int)$status);
}

if ($options['install']) {
    echo "Acceptance tests site installed for sites:".PHP_EOL;

        for ($i = $options['fromrun']; $i <= $options['torun']; $i++) {
        if (empty($CFG->behat_parallel_run[$i - 1]['behat_wwwroot'])) {
            echo $CFG->behat_wwwroot . "/" . BEHAT_PARALLEL_SITE_NAME . $i . PHP_EOL;
        } else {
            echo $CFG->behat_parallel_run[$i - 1]['behat_wwwroot'] . PHP_EOL;
        }

    }
} else if ($options['drop']) {
    echo "Acceptance tests site dropped for " . $options['parallel'] . " parallel sites" . PHP_EOL;

} else if ($options['enable']) {
    echo "Acceptance tests environment enabled on $CFG->behat_wwwroot, to run the tests use:" . PHP_EOL;
    echo behat_command::get_behat_command(true, true);
    echo PHP_EOL;

} else if ($options['disable']) {
    echo "Acceptance tests environment disabled for " . $options['parallel'] . " parallel sites" . PHP_EOL;

} else if ($options['diag']) {
    } else {
    echo $help;
    chdir($cwd);
    exit(1);
}

chdir($cwd);
exit(0);


function commands_to_execute($options) {
    $removeoptions = array('maxruns', 'fromrun', 'torun');
    $cmds = array();
    $extraoptions = $options;
    $extra = "";

        foreach ($removeoptions as $ro) {
        $extraoptions[$ro] = null;
        unset($extraoptions[$ro]);
    }

    foreach ($extraoptions as $option => $value) {
        if ($options[$option]) {
            $extra .= " --$option";
            if ($value) {
                $extra .= "=$value";
            }
        }
    }

    if (empty($options['parallel'])) {
        $cmds = "php util_single_run.php " . $extra;
    } else {
                for ($i = $options['fromrun']; $i <= $options['torun']; $i++) {
            $prefix = BEHAT_PARALLEL_SITE_NAME . $i;
            $cmds[$prefix] = "php util_single_run.php " . $extra . " --run=" . $i . " 2>&1";
        }
    }
    return $cmds;
}


function print_combined_drop_output($processes) {
    $exitcodes = array();
    $maxdotsonline = 70;
    $remainingprintlen = $maxdotsonline;
    $progresscount = 0;
    echo "Dropping tables:" . PHP_EOL;

    while (count($exitcodes) != count($processes)) {
        usleep(10000);
        foreach ($processes as $name => $process) {
            if ($process->isRunning()) {
                $op = $process->getIncrementalOutput();
                if (trim($op)) {
                    $update = preg_filter('#^\s*([FS\.\-]+)(?:\s+\d+)?\s*$#', '$1', $op);
                    $strlentoprint = strlen($update);

                                        if ($strlentoprint < $remainingprintlen) {
                        echo $update;
                        $remainingprintlen = $remainingprintlen - $strlentoprint;
                    } else if ($strlentoprint == $remainingprintlen) {
                        $progresscount += $maxdotsonline;
                        echo $update . " " . $progresscount . PHP_EOL;
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
                                $process->clearOutput();
                $exitcodes[$name] = $process->getExitCode();
            }
        }
    }

    echo PHP_EOL;
    return $exitcodes;
}


function print_combined_install_output($processes) {
    $exitcodes = array();
    $line = array();

            if (defined('BEHAT_MAX_CMD_LINE_OUTPUT') && BEHAT_MAX_CMD_LINE_OUTPUT) {
        $lengthofprocessline = (int)max(10, BEHAT_MAX_CMD_LINE_OUTPUT / count($processes));
    } else {
        $lengthofprocessline = (int)max(10, 80 / count($processes));
    }

    echo "Installing behat site for " . count($processes) . " parallel behat run" . PHP_EOL;

        foreach ($processes as $name => $process) {
                if ($lengthofprocessline < strlen($name + 2)) {
            $name = substr($name, -5);
        }
                $line[$name] = str_pad('[' . $name . '] ', $lengthofprocessline + 1);
    }
    ksort($line);
    $tableheader = array_keys($line);
    echo implode("", $line) . PHP_EOL;

        while (count($exitcodes) != count($processes)) {
        usleep(50000);
        $poutput = array();
                foreach ($processes as $name => $process) {
            if ($process->isRunning()) {
                $output = $process->getIncrementalOutput();
                if (trim($output)) {
                    $poutput[$name] = explode(PHP_EOL, $output);
                }
            } else {
                                $exitcodes[$name] = $process->getExitCode();
            }
        }
        ksort($poutput);

                $maxdepth = 0;
        foreach ($poutput as $pout) {
            $pdepth = count($pout);
            $maxdepth = $pdepth >= $maxdepth ? $pdepth : $maxdepth;
        }

                for ($i = 0; $i <= $maxdepth; $i++) {
            $pline = "";
            foreach ($tableheader as $name) {
                $po = empty($poutput[$name][$i]) ? "" : substr($poutput[$name][$i], 0, $lengthofprocessline - 1);
                $po = str_pad($po, $lengthofprocessline);
                $pline .= "|". $po;
            }
            if (trim(str_replace("|", "", $pline))) {
                echo $pline . PHP_EOL;
            }
        }
        unset($poutput);
        $poutput = null;

    }
    echo PHP_EOL;
    return $exitcodes;
}


function print_sequential_output($processes, $showprefix = true) {
    $status = false;
    foreach ($processes as $name => $process) {
        $shownname = false;
        while ($process->isRunning()) {
            $op = $process->getIncrementalOutput();
            if (trim($op)) {
                                if ($showprefix && !$shownname) {
                    echo '[' . $name . '] ';
                    $shownname = true;
                }
                echo $op;
            }
        }
                $exitcode = $process->getExitCode();
        if ($exitcode != 0) {
            exit($exitcode);
        }
        $status = $status || (bool)$exitcode;
    }
    return $status;
}
