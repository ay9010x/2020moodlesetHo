<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_configtext('grade_report_historyperpage',
        new lang_string('historyperpage', 'gradereport_history'),
        new lang_string('historyperpage_help', 'gradereport_history'),
        50
    ));

}
