<?php



defined('MOODLE_INTERNAL') || die;


function report_log_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/log:view', $context)) {
        $url = new moodle_url('/report/log/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_log'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


function report_log_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_reader) {
        return true;
    }
    return false;
}


function report_log_extend_navigation_user($navigation, $user, $course) {
    list($all, $today) = report_log_can_access_user_report($user, $course);

    if ($today) {
        $url = new moodle_url('/report/log/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'today'));
        $navigation->add(get_string('todaylogs'), $url);
    }
    if ($all) {
        $url = new moodle_url('/report/log/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'all'));
        $navigation->add(get_string('alllogs'), $url);
    }
}


function report_log_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    $today = false;
    $all = false;

    if (has_capability('report/log:view', $coursecontext)) {
        $today = true;
    }
    if (has_capability('report/log:viewtoday', $coursecontext)) {
        $all = true;
    }

    if ($today and $all) {
        return array(true, true);
    }

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return array(true, true);
        }

    } else if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return array(true, true);
        }
    }

    return array($all, $today);
}


function report_log_extend_navigation_module($navigation, $cm) {
    if (has_capability('report/log:view', context_course::instance($cm->course))) {
        $url = new moodle_url('/report/log/index.php', array('chooselog'=>'1','id'=>$cm->course,'modid'=>$cm->id));
        $navigation->add(get_string('logs'), $url, navigation_node::TYPE_SETTING, null, 'logreport');
    }
}


function report_log_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                => get_string('page-x', 'pagetype'),
        'report-*'         => get_string('page-report-x', 'pagetype'),
        'report-log-*'     => get_string('page-report-log-x',  'report_log'),
        'report-log-index' => get_string('page-report-log-index',  'report_log'),
        'report-log-user'  => get_string('page-report-log-user',  'report_log')
    );
    return $array;
}


function report_log_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (empty($course)) {
                $course = get_fast_modinfo(SITEID)->get_course();
    }
    list($all, $today) = report_log_can_access_user_report($user, $course);
    if ($today) {
                $url = new moodle_url('/report/log/user.php',
            array('id' => $user->id, 'course' => $course->id, 'mode' => 'today'));
        $node = new core_user\output\myprofile\node('reports', 'todayslogs', get_string('todaylogs'), null, $url);
        $tree->add_node($node);
    }

    if ($all) {
                $url = new moodle_url('/report/log/user.php',
            array('id' => $user->id, 'course' => $course->id, 'mode' => 'all'));
        $node = new core_user\output\myprofile\node('reports', 'alllogs', get_string('alllogs'), null, $url);
        $tree->add_node($node);
    }
    return true;
}
