<?php



defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('reportstats', get_string('pluginname', 'report_stats'), "$CFG->wwwroot/report/stats/index.php", 'report/stats:view', empty($CFG->enablestats)));

$settings = null;
