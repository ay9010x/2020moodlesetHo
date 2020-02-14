<?php



define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once("$CFG->libdir/clilib.php");
require_once("$CFG->libdir/adminlib.php");


list($options, $unrecognized) = cli_get_params(array('enable'=>false, 'enablelater'=>0, 'enableold'=>false, 'disable'=>false, 'help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Maintenance mode settings.
Current status displayed if not option specified.

Options:
--enable              Enable CLI maintenance mode
--enablelater=MINUTES Number of minutes before entering CLI maintenance mode
--enableold           Enable legacy half-maintenance mode
--disable             Disable maintenance mode
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php admin/cli/maintenance.php
"; 
    echo $help;
    die;
}

cli_heading(get_string('sitemaintenancemode', 'admin')." ($CFG->wwwroot)");

if ($options['enablelater']) {
    if (file_exists("$CFG->dataroot/climaintenance.html")) {
                echo get_string('clistatusenabled', 'admin')."\n";
        return 1;
    }

    $time = time() + ($options['enablelater']*60);
    set_config('maintenance_later', $time);

    echo get_string('clistatusenabledlater', 'admin', userdate($time))."\n";
    return 0;

} else if ($options['enable']) {
    if (file_exists("$CFG->dataroot/climaintenance.html")) {
            } else {
        enable_cli_maintenance_mode();
    }
    set_config('maintenance_enabled', 0);
    unset_config('maintenance_later');
    echo get_string('sitemaintenanceoncli', 'admin')."\n";
    exit(0);

} else if ($options['enableold']) {
    set_config('maintenance_enabled', 1);
    unset_config('maintenance_later');
    echo get_string('sitemaintenanceon', 'admin')."\n";
    exit(0);

} else if ($options['disable']) {
    set_config('maintenance_enabled', 0);
    unset_config('maintenance_later');
    if (file_exists("$CFG->dataroot/climaintenance.html")) {
        unlink("$CFG->dataroot/climaintenance.html");
    }
    echo get_string('sitemaintenanceoff', 'admin')."\n";
    exit(0);
}

if (!empty($CFG->maintenance_enabled) or file_exists("$CFG->dataroot/climaintenance.html")) {
    echo get_string('clistatusenabled', 'admin')."\n";

} else if (isset($CFG->maintenance_later)) {
    echo get_string('clistatusenabledlater', 'admin', userdate($CFG->maintenance_later))."\n";

} else {
    echo get_string('clistatusdisabled', 'admin')."\n";
}
