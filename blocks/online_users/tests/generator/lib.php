<?php



defined('MOODLE_INTERNAL') || die();



class block_online_users_generator extends testing_block_generator {

    
    public function create_logged_in_users() {
        global $DB;

        $generator = advanced_testcase::getDataGenerator();
        $data = array();

                $course1 = $generator->create_course();
        $data['course1'] = $course1;
        $course2 = $generator->create_course();
        $data['course2'] = $course2;

                for ($i = 1; $i <= 9; $i++) {
            $user = $generator->create_user();
            $DB->set_field('user', 'lastaccess', time(), array('id' => $user->id));
            $generator->enrol_user($user->id, $course1->id);
            $DB->insert_record('user_lastaccess', array('userid' => $user->id, 'courseid' => $course1->id, 'timeaccess' => time()));
            $data['user' . $i] = $user;
        }
                for ($i = 10; $i <= 12; $i++) {
            $user = $generator->create_user();
            $DB->set_field('user', 'lastaccess', time(), array('id' => $user->id));
            $data['user' . $i] = $user;
        }

                $group1 = $generator->create_group(array('courseid' => $course1->id));
        $data['group1'] = $group1;
        $group2 = $generator->create_group(array('courseid' => $course1->id));
        $data['group2'] = $group2;
        $group3 = $generator->create_group(array('courseid' => $course1->id));
        $data['group3'] = $group3;

                $generator->create_group_member(array('groupid' => $group1->id, 'userid' => $data['user1']->id));
        $generator->create_group_member(array('groupid' => $group1->id, 'userid' => $data['user2']->id));
        $generator->create_group_member(array('groupid' => $group1->id, 'userid' => $data['user3']->id));

                $generator->create_group_member(array('groupid' => $group2->id, 'userid' => $data['user3']->id));
        $generator->create_group_member(array('groupid' => $group2->id, 'userid' => $data['user4']->id));
        $generator->create_group_member(array('groupid' => $group2->id, 'userid' => $data['user5']->id));
        $generator->create_group_member(array('groupid' => $group2->id, 'userid' => $data['user6']->id));

        return $data;     }
}
