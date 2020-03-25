<?php



defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $searchurl = $CFG->wwwroot . '/report/search/index.php';
    $ADMIN->add('reports', new admin_externalpage('reportsearch', new lang_string('pluginname', 'report_search'),
        $searchurl));

        $settings = null;
}
