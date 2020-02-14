<?php



defined('MOODLE_INTERNAL') || die;


function report_participation_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/participation:view', $context)) {
        $url = new moodle_url('/report/participation/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_participation'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


function report_participation_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                          => get_string('page-x', 'pagetype'),
        'report-*'                   => get_string('page-report-x', 'pagetype'),
        'report-participation-*'     => get_string('page-report-participation-x',  'report_participation'),
        'report-participation-index' => get_string('page-report-participation-index',  'report_participation'),
    );
    return $array;
}


function report_participation_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_internal_table_reader || $instance instanceof \logstore_legacy\log\store) {
        return true;
    }
    return false;
}
