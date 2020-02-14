<?php


namespace core\task;


class delete_incomplete_users_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskdeleteincompleteusers', 'admin');
    }

    
    public function execute() {
        global $CFG, $DB;

        $timenow = time();

                if (!empty($CFG->deleteincompleteusers)) {
            $cuttime = $timenow - ($CFG->deleteincompleteusers * 3600);
            $rs = $DB->get_recordset_sql ("SELECT *
                                               FROM {user}
                                           WHERE confirmed = 1 AND lastaccess > 0
                                               AND lastaccess < ? AND deleted = 0
                                               AND (lastname = '' OR firstname = '' OR email = '')",
                                           array($cuttime));
            foreach ($rs as $user) {
                if (isguestuser($user) or is_siteadmin($user)) {
                    continue;
                }
                if ($user->lastname !== '' and $user->firstname !== '' and $user->email !== '') {
                                        continue;
                }
                delete_user($user);
                mtrace(" Deleted not fully setup user $user->username ($user->id)");
            }
            $rs->close();
        }
    }

}
