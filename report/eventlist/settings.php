<?php



defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $url = $CFG->wwwroot . '/report/eventlist/index.php';
    $ADMIN->add('reports', new admin_externalpage('reporteventlists', get_string('pluginname', 'report_eventlist'), $url));

        $settings = null;
}
