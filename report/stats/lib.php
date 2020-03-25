<?php



defined('MOODLE_INTERNAL') || die;


function report_stats_extend_navigation_course($navigation, $course, $context) {
    global $CFG;
    if (empty($CFG->enablestats)) {
        return;
    }
    if (has_capability('report/stats:view', $context)) {
        $url = new moodle_url('/report/stats/index.php', array('course'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_stats'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


function report_stats_extend_navigation_user($navigation, $user, $course) {
    global $CFG;
    if (empty($CFG->enablestats)) {
        return;
    }
    if (report_stats_can_access_user_report($user, $course)) {
        $url = new moodle_url('/report/stats/user.php', array('id'=>$user->id, 'course'=>$course->id));
        $navigation->add(get_string('stats'), $url);
    }
}


function report_stats_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    if (has_capability('report/stats:view', $coursecontext)) {
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


function report_stats_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                  => get_string('page-x', 'pagetype'),
        'report-*'           => get_string('page-report-x', 'pagetype'),
        'report-stats-*'     => get_string('page-report-stats-x',  'report_stats'),
        'report-stats-index' => get_string('page-report-stats-index',  'report_stats'),
        'report-stats-user'  => get_string('page-report-stats-user',  'report_stats')
    );
    return $array;
}


function report_stats_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_internal_table_reader || $instance instanceof \logstore_legacy\log\store) {
        return true;
    }
    return false;
}


function report_stats_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG;
    if (empty($CFG->enablestats)) {
        return false;
    }
    if (empty($course)) {
                $course = get_fast_modinfo(SITEID)->get_course();
    }
    if (report_stats_can_access_user_report($user, $course)) {
        $url = new moodle_url('/report/stats/user.php', array('id' => $user->id, 'course' => $course->id));
        $node = new core_user\output\myprofile\node('reports', 'stats', get_string('stats'), null, $url);
        $tree->add_node($node);
    }
}
