<?php



defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('reportloglive', get_string('pluginname', 'report_loglive'),
        "$CFG->wwwroot/report/loglive/index.php", 'report/loglive:view'));

$settings = null;
