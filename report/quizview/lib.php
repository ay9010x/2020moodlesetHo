<?php

defined('MOODLE_INTERNAL') || die;

function report_quizview_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/quizview:view', $context)) {
        $url = new moodle_url('/report/quizview/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_quizview'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}