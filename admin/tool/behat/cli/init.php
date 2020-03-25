<?php



if (isset($_SERVER['REMOTE_ADDR'])) {
    die(); }

if (function_exists('opcache_reset')) {
    opcache_reset();
}

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require_once(__DIR__ . '/../../../../lib/clilib.php');
require_once(__DIR__ . '/../../../../lib/behat/lib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'parallel' => 0,
        'maxruns'  => false,
        'help'     => false,
        'fromrun'  => 1,
        'torun'    => 0,
    ),
    array(
        'j' => 'parallel',
        'm' => 'maxruns',
        'h' => 'help',
    )
);

$help = "
Behat utilities to initialise behat tests

Usage:
  php init.php [--parallel=value [--maxruns=value] [--fromrun=value --torun=value]] [--help]

Options:
-j, --parallel Number of parallel behat run to initialise
-m, --maxruns  Max parallel processes to be executed at one time.
--fromrun      Execute run starting from (Used for parallel runs on different vms)
--torun        Execute run till (Used for parallel runs on different vms)

-h, --help     Print out this help

Example from Moodle root directory:
\$ php admin/tool/behat/cli/init.php --parallel=2

More info in http://docs.moodle.org/dev/Acceptance_testing#Running_tests
";

if (!empty($options['help'])) {
    echo $help;
    exit(0);
}

$utilfile = 'util_single_run.php';
$paralleloption = "";
if ($options['parallel'] && $options['parallel'] > 1) {
    $utilfile = 'util.php';
    $paralleloption = "";
    foreach ($options as $option => $value) {
        if ($value) {
            $paralleloption .= " --$option=\"$value\"";
        }
    }
}

$cwd = getcwd();
$output = null;

testing_update_composer_dependencies();

chdir(__DIR__);
exec("php $utilfile --diag $paralleloption", $output, $code);

if ($code == 0) {
    echo "Behat test environment already installed\n";

} else if ($code == BEHAT_EXITCODE_INSTALL) {
        chdir(__DIR__);
    passthru("php $utilfile --install $paralleloption", $code);
    if ($code != 0) {
        chdir($cwd);
        exit($code);
    }

} else if ($code == BEHAT_EXITCODE_REINSTALL) {
        chdir(__DIR__);
    passthru("php $utilfile --drop $paralleloption", $code);
    if ($code != 0) {
        chdir($cwd);
        exit($code);
    }

    chdir(__DIR__);
    passthru("php $utilfile --install $paralleloption", $code);
    if ($code != 0) {
        chdir($cwd);
        exit($code);
    }

} else {
        echo implode("\n", $output)."\n";
    chdir($cwd);
    exit($code);
}

chdir(__DIR__);
passthru("php $utilfile --enable $paralleloption", $code);
if ($code != 0) {
    echo "Error enabling site" . PHP_EOL;
    chdir($cwd);
    exit($code);
}

exit(0);
