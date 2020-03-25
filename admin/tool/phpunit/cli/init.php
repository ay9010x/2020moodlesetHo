<?php



if (isset($_SERVER['REMOTE_ADDR'])) {
    die; }

if (function_exists('opcache_reset')) {
    opcache_reset();
}

define('IGNORE_COMPONENT_CACHE', true);

require_once(__DIR__.'/../../../../lib/clilib.php');
require_once(__DIR__.'/../../../../lib/phpunit/bootstraplib.php');
require_once(__DIR__.'/../../../../lib/testing/lib.php');

echo "Initialising Moodle PHPUnit test environment...\n";
testing_update_composer_dependencies();

$output = null;
exec('php --version', $output, $code);
if ($code != 0) {
    phpunit_bootstrap_error(1, 'Can not execute \'php\' binary.');
}

chdir(__DIR__);
$output = null;
exec("php util.php --diag", $output, $code);
if ($code == 0) {
    
} else if ($code == PHPUNIT_EXITCODE_INSTALL) {
    passthru("php util.php --install", $code);
    if ($code != 0) {
        exit($code);
    }

} else if ($code == PHPUNIT_EXITCODE_REINSTALL) {
    passthru("php util.php --drop", $code);
    passthru("php util.php --install", $code);
    if ($code != 0) {
        exit($code);
    }

} else {
    echo implode("\n", $output)."\n";
    exit($code);
}

passthru("php util.php --buildconfig", $code);

echo "\n";
echo "PHPUnit test environment setup complete.\n";
exit(0);
