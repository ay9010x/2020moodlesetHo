<?php



define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');

$help =
    "Create sample zip file for testing
Example:
  \$php zip_create_test_file.php test.zip
";

if (count($_SERVER['argv']) != 2 or file_exists($_SERVER['argv'][1])) {
    echo $help;
    exit(0);
}

$archive = $_SERVER['argv'][1];

$packer = get_file_packer('application/zip');

$file = __DIR__.'/test.txt';
$files = array(
    'test.test' => $file,
    'testíček.txt' => $file,
    'Prüfung.txt' => $file,
    '测试.txt' => $file,
    '試験.txt' => $file,
    'Žluťoučký/Koníček.txt' => $file,
);

$packer->archive_to_pathname($files, $archive);
