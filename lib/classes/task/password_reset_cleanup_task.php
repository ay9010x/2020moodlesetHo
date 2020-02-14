<?php


namespace core\task;


class password_reset_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskpasswordresetcleanup', 'admin');
    }

    
    public function execute() {
        global $DB, $CFG;

                                        $pwresettime = isset($CFG->pwresettime) ? $CFG->pwresettime : 1800;
        $earliestvalid = time() - $pwresettime - DAYSECS;
        $DB->delete_records_select('user_password_resets', "timerequested < ?", array($earliestvalid));
        mtrace(' Cleaned up old password reset records');

    }

}
