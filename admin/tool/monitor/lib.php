<?php



defined('MOODLE_INTERNAL') || die;


function tool_monitor_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('tool/monitor:managerules', $context) && get_config('tool_monitor', 'enablemonitor')) {
        $url = new moodle_url('/admin/tool/monitor/managerules.php', array('courseid' => $course->id));
        $settingsnode = navigation_node::create(get_string('managerules', 'tool_monitor'), $url, navigation_node::TYPE_SETTING,
                null, null, new pix_icon('i/settings', ''));
        $reportnode = $navigation->get('coursereports');

        if (isset($settingsnode) && !empty($reportnode)) {
            $reportnode->add_node($settingsnode);
        }
    }
}


function tool_monitor_extend_navigation_frontpage($navigation, $course, $context) {

    if (has_capability('tool/monitor:managerules', $context)) {
        $url = new moodle_url('/admin/tool/monitor/managerules.php', array('courseid' => $course->id));
        $settingsnode = navigation_node::create(get_string('managerules', 'tool_monitor'), $url, navigation_node::TYPE_SETTING,
                null, null, new pix_icon('i/settings', ''));
        $reportnode = $navigation->get('frontpagereports');

        if (isset($settingsnode) && !empty($reportnode)) {
            $reportnode->add_node($settingsnode);
        }
    }
}


function tool_monitor_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    global $USER, $SITE;

        if (get_config('tool_monitor', 'enablemonitor') && $USER->id == $user->id) {
                if ($courses = tool_monitor_get_user_courses()) {
            $url = new moodle_url('/admin/tool/monitor/index.php');
            $subsnode = navigation_node::create(get_string('managesubscriptions', 'tool_monitor'), $url,
                    navigation_node::TYPE_SETTING, null, 'monitor', new pix_icon('i/settings', ''));

            if (isset($subsnode) && !empty($navigation)) {
                $navigation->add_node($subsnode);
            }
        }
    }
}


function tool_monitor_get_user_courses() {
    $orderby = 'visible DESC, sortorder ASC';
    $options = array();
    if (has_capability('tool/monitor:subscribe', context_system::instance())) {
        $options[0] = get_string('site');
    }
    if ($courses = get_user_capability_course('tool/monitor:subscribe', null, true, 'fullname, visible', $orderby)) {
        foreach ($courses as $course) {
            $coursectx = context_course::instance($course->id);
            if ($course->visible || has_capability('moodle/course:viewhiddencourses', $coursectx)) {
                $options[$course->id] = format_string($course->fullname, true, array('context' => $coursectx));
            }
        }
    }
        if (count($options) < 1) {
        return false;
    } else {
        return $options;
    }
}
