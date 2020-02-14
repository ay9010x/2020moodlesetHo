<?php

defined('MOODLE_INTERNAL') || die;

function report_materialview_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/materialview:view', $context)) {
        $url = new moodle_url('/report/materialview/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_materialview'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}