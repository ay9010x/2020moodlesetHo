<?php



define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/clilib.php');

$help = "Validate database structure

Options:
-h, --help            Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php admin/cli/check_database_schema.php
";

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
    ),
    array(
        'h' => 'help',
    )
);

if ($options['help']) {
    echo $help;
    exit(0);
}

if (empty($CFG->version)) {
    echo "Database is not yet installed.\n";
    exit(2);
}

$dbmanager = $DB->get_manager();
$schema = $dbmanager->get_install_xml_schema();

if (!$errors = $dbmanager->check_database_schema($schema)) {
    echo "Database structure is ok.\n";
    exit(0);
}

foreach ($errors as $table => $items) {
    cli_separator();
    echo "$table\n";
    foreach ($items as $item) {
        echo " * $item\n";
    }
}
cli_separator();

exit(1);
