<?php



defined('MOODLE_INTERNAL') || die;


function report_usersessions_extend_navigation_user($navigation, $user, $course) {
    global $USER;

    if (isguestuser() or !isloggedin()) {
        return;
    }

    if (\core\session\manager::is_loggedinas() or $USER->id != $user->id) {
                return;
    }

    $context = context_user::instance($USER->id);
    if (has_capability('report/usersessions:manageownsessions', $context)) {
        $navigation->add(get_string('navigationlink', 'report_usersessions'),
            new moodle_url('/report/usersessions/user.php'), $navigation::TYPE_SETTING);
    }
}


function report_usersessions_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER;

    if (isguestuser() or !isloggedin()) {
        return;
    }

    if (\core\session\manager::is_loggedinas() or $USER->id != $user->id) {
                return;
    }

    $context = context_user::instance($USER->id);
    if (has_capability('report/usersessions:manageownsessions', $context)) {
        $node = new core_user\output\myprofile\node('reports', 'usersessions',
                get_string('navigationlink', 'report_usersessions'), null, new moodle_url('/report/usersessions/user.php'));
        $tree->add_node($node);
    }
    return true;
}
