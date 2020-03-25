<?php



defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/lib.php');


function report_usersessions_format_duration($duration) {

                    
    if ($duration < 60) {
        return get_string('now');
    }

    if ($duration < 60 * 60 * 2) {
        $minutes = (int)($duration / 60);
        $ago = $minutes . ' ' . get_string('minutes');
        return get_string('ago', 'core_message', $ago);
    }

    $hours = (int)($duration / (60 * 60));
    $ago = $hours . ' ' . get_string('hours');
    return get_string('ago', 'core_message', $ago);
}


function report_usersessions_format_ip($ip) {
    if (strpos($ip, ':') !== false) {
                return $ip;
    }
    $url = new moodle_url('/iplookup/index.php', array('ip' => $ip));
    return html_writer::link($url, $ip);
}


function report_usersessions_kill_session($id) {
    global $DB, $USER;

    $session = $DB->get_record('sessions', array('id' => $id, 'userid' => $USER->id), 'id, sid');

    if (!$session or $session->sid === session_id()) {
                return;
    }

    \core\session\manager::kill_session($session->sid);
}
