<?php

defined('MOODLE_INTERNAL') || die;

function report_forumview_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/forumview:view', $context)) {
        $url = new moodle_url('/report/forumview/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_forumview'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}