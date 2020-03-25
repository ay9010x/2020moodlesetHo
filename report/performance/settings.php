<?php



defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('reportperformance', get_string('pluginname', 'report_performance'),
        $CFG->wwwroot."/report/performance/index.php", 'report/performance:view'));

$settings = null;
