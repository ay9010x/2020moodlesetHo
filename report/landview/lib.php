<?php

defined('MOODLE_INTERNAL') || die;

function report_landview_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/landview:view', $context)) {
        $url = new moodle_url('/report/landview/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_landview'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}