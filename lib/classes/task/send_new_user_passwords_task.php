<?php


namespace core\task;


class send_new_user_passwords_task extends scheduled_task {

    
    public function get_name() {
        return get_string('tasksendnewuserpasswords', 'admin');
    }

    
    public function execute() {
        global $DB;

                if ($DB->count_records('user_preferences', array('name' => 'create_password', 'value' => '1'))) {
            mtrace('Creating passwords for new users...');
            $usernamefields = get_all_user_name_fields(true, 'u');
            $newusers = $DB->get_recordset_sql("SELECT u.id as id, u.email, u.auth, u.deleted,
                                                     u.suspended, u.emailstop, u.mnethostid, u.mailformat,
                                                     $usernamefields, u.username, u.lang,
                                                     p.id as prefid
                                                FROM {user} u
                                                JOIN {user_preferences} p ON u.id=p.userid
                                               WHERE p.name='create_password' AND p.value='1' AND
                                                     u.email !='' AND u.suspended = 0 AND
                                                     u.auth != 'nologin' AND u.deleted = 0");

                        foreach ($newusers as $newuser) {
                                                                                if (setnew_password_and_mail($newuser, true)) {
                    unset_user_preference('create_password', $newuser);
                    set_user_preference('auth_forcepasswordchange', 1, $newuser);
                } else {
                    trigger_error("Could not create and mail new user password!");
                }
            }
            $newusers->close();
        }
    }

}
