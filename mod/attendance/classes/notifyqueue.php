<?php



defined('MOODLE_INTERNAL') || die();


class mod_attendance_notifyqueue {

    
    public static function show() {
        global $SESSION, $OUTPUT;

        if (isset($SESSION->mod_attendance_notifyqueue)) {
            foreach ($SESSION->mod_attendance_notifyqueue as $message) {
                echo $OUTPUT->notification($message->message, 'notify'.$message->type);
            }
            unset($SESSION->mod_attendance_notifyqueue);
        }
    }

    
    public static function notify_problem($message) {
        self::queue_message($message, \core\output\notification::NOTIFY_PROBLEM);
    }

    
    public static function notify_message($message) {
        self::queue_message($message, \core\output\notification::NOTIFY_MESSAGE);
    }

    
    public static function notify_success($message) {
        self::queue_message($message, \core\output\notification::NOTIFY_SUCCESS);
    }

    
    private static function queue_message($message, $messagetype=\core\output\notification::NOTIFY_MESSAGE) {
        global $SESSION;

        if (!isset($SESSION->mod_attendance_notifyqueue)) {
            $SESSION->mod_attendance_notifyqueue = array();
        }
        $m = new stdclass();
        $m->type = $messagetype;
        $m->message = $message;
        $SESSION->mod_attendance_notifyqueue[] = $m;
    }
}
