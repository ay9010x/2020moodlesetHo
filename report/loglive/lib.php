<?php



defined('MOODLE_INTERNAL') || die;


function report_loglive_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/loglive:view', $context)) {
        $url = new moodle_url('/report/loglive/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_loglive'), $url, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('i/report', ''));
    }
}


function report_loglive_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_reader) {
        return true;
    }
    return false;
}
