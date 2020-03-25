<?php



defined('MOODLE_INTERNAL') || die;


function report_outline_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/outline:view', $context)) {
        $url = new moodle_url('/report/outline/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_outline'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


function report_outline_extend_navigation_user($navigation, $user, $course) {
    if (report_outline_can_access_user_report($user, $course)) {
        $url = new moodle_url('/report/outline/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'outline'));
        $navigation->add(get_string('outlinereport'), $url);
        $url = new moodle_url('/report/outline/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'complete'));
        $navigation->add(get_string('completereport'), $url);
    }
}


function report_outline_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    if (has_capability('report/outline:view', $coursecontext)) {
        return true;
    }

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return true;
        }

    } else if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return true;
        }
    }

    return false;
}


function report_outline_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                    => get_string('page-x', 'pagetype'),
        'report-*'             => get_string('page-report-x', 'pagetype'),
        'report-outline-*'     => get_string('page-report-outline-x',  'report_outline'),
        'report-outline-index' => get_string('page-report-outline-index',  'report_outline'),
        'report-outline-user'  => get_string('page-report-outline-user',  'report_outline')
    );
    return $array;
}


function report_outline_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_internal_table_reader || $instance instanceof \logstore_legacy\log\store) {
        return true;
    }
    return false;
}


function report_outline_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (empty($course)) {
                $course = get_fast_modinfo(SITEID)->get_course();
    }
    if (report_outline_can_access_user_report($user, $course)) {
        $url = new moodle_url('/report/outline/user.php',
                array('id' => $user->id, 'course' => $course->id, 'mode' => 'outline'));
        $node = new core_user\output\myprofile\node('reports', 'outline', get_string('outlinereport'), null, $url);
        $tree->add_node($node);
        $url = new moodle_url('/report/outline/user.php',
            array('id' => $user->id, 'course' => $course->id, 'mode' => 'complete'));
        $node = new core_user\output\myprofile\node('reports', 'complete', get_string('completereport'), null, $url);
        $tree->add_node($node);
    }
}
