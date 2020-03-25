<?php



require_once(__DIR__ . '/../testing/lib.php');

define('PHPUNIT_EXITCODE_PHPUNITMISSING', 129);
define('PHPUNIT_EXITCODE_PHPUNITWRONG', 130);
define('PHPUNIT_EXITCODE_PHPUNITEXTMISSING', 131);
define('PHPUNIT_EXITCODE_CONFIGERROR', 135);
define('PHPUNIT_EXITCODE_CONFIGWARNING', 136);
define('PHPUNIT_EXITCODE_INSTALL', 140);
define('PHPUNIT_EXITCODE_REINSTALL', 141);


function phpunit_bootstrap_error($errorcode, $text = '') {
    switch ($errorcode) {
        case 0:
                        break;
        case 1:
            $text = 'Error: '.$text;
            break;
        case PHPUNIT_EXITCODE_PHPUNITMISSING:
            $text = "Can not find PHPUnit library, to install use: php composer.phar install";
            break;
        case PHPUNIT_EXITCODE_PHPUNITWRONG:
            $text = 'Moodle requires PHPUnit 3.6.x, '.$text.' is not compatible';
            break;
        case PHPUNIT_EXITCODE_PHPUNITEXTMISSING:
            $text = 'Moodle can not find required PHPUnit extension '.$text;
            break;
        case PHPUNIT_EXITCODE_CONFIGERROR:
            $text = "Moodle PHPUnit environment configuration error:\n".$text;
            break;
        case PHPUNIT_EXITCODE_CONFIGWARNING:
            $text = "Moodle PHPUnit environment configuration warning:\n".$text;
            break;
        case PHPUNIT_EXITCODE_INSTALL:
            $path = testing_cli_argument_path('/admin/tool/phpunit/cli/init.php');
            $text = "Moodle PHPUnit environment is not initialised, please use:\n php $path";
            break;
        case PHPUNIT_EXITCODE_REINSTALL:
            $path = testing_cli_argument_path('/admin/tool/phpunit/cli/init.php');
            $text = "Moodle PHPUnit environment was initialised for different version, please use:\n php $path";
            break;
        default:
            $text = empty($text) ? '' : ': '.$text;
            $text = 'Unknown error '.$errorcode.$text;
            break;
    }

    testing_error($errorcode, $text);
}
