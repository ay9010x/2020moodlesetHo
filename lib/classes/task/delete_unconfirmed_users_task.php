<?php


namespace core\task;


class delete_unconfirmed_users_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskdeleteunconfirmedusers', 'admin');
    }

    
    public function execute() {
        global $CFG, $DB;

        $timenow = time();

                if (!empty($CFG->deleteunconfirmed)) {
            $cuttime = $timenow - ($CFG->deleteunconfirmed * 3600);
            $rs = $DB->get_recordset_sql ("SELECT *
                                             FROM {user}
                                            WHERE confirmed = 0 AND firstaccess > 0
                                                  AND firstaccess < ? AND deleted = 0", array($cuttime));
            foreach ($rs as $user) {
                delete_user($user);                 $DB->delete_records('user', array('id' => $user->id));                 mtrace(" Deleted unconfirmed user for ".fullname($user, true)." ($user->id)");
            }
            $rs->close();
        }
    }

}
