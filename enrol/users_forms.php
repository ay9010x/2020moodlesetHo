<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_users_assign_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $user       = $this->_customdata['user'];
        $course     = $this->_customdata['course'];
        $context    = context_course::instance($course->id);
        $assignable = $this->_customdata['assignable'];
        $assignable = array_reverse($assignable, true); 
        $ras = get_user_roles($context, $user->id, true);
        foreach ($ras as $ra) {
            unset($assignable[$ra->roleid]);
        }

        $mform->addElement('header','general', fullname($user));

        $mform->addElement('select', 'roleid', get_string('addrole', 'role'), $assignable);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'user');
        $mform->setType('user', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'ifilter');
        $mform->setType('ifilter', PARAM_ALPHA);

        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_INT);

        $mform->addElement('hidden', 'perpage');
        $mform->setType('perpage', PARAM_INT);

        $mform->addElement('hidden', 'sort');
        $mform->setType('sort', PARAM_ALPHA);

        $mform->addElement('hidden', 'dir');
        $mform->setType('dir', PARAM_ALPHA);

        $this->add_action_buttons();

        $this->set_data(array('action'=>'assign', 'user'=>$user->id));
    }
}

class enrol_users_addmember_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $user     = $this->_customdata['user'];
        $course   = $this->_customdata['course'];
        $context  = context_course::instance($course->id, IGNORE_MISSING);
        $allgroups = $this->_customdata['allgroups'];
        $usergroups = groups_get_all_groups($course->id, $user->id, 0, 'g.id');

        $options = array();
        foreach ($allgroups as $group) {
            if (isset($usergroups[$group->id])) {
                continue;
            }
            $options[$group->id] = $group->name;
        }

        $mform->addElement('header','general', fullname($user));

        $mform->addElement('select', 'groupids', get_string('addgroup', 'group'), $options, array('multiple' => 'multiple'));
        $mform->addRule('groupids', null, 'required');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'user');
        $mform->setType('user', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'ifilter');
        $mform->setType('ifilter', PARAM_ALPHA);

        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_INT);

        $mform->addElement('hidden', 'perpage');
        $mform->setType('perpage', PARAM_INT);

        $mform->addElement('hidden', 'sort');
        $mform->setType('sort', PARAM_ALPHA);

        $mform->addElement('hidden', 'dir');
        $mform->setType('dir', PARAM_ALPHA);

        $this->add_action_buttons();

        $this->set_data(array('action'=>'addmember', 'user'=>$user->id));
    }
}



class enrol_users_filter_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $manager = $this->_customdata['manager'];

        $mform = $this->_form;

                $mform->addElement('text', 'search', get_string('search'));
        $mform->setType('search', PARAM_RAW);

                $mform->addElement('select', 'ifilter', get_string('enrolmentinstances', 'enrol'),
                array(0 => get_string('all')) + (array)$manager->get_enrolment_instance_names());

                                        $allroles = $manager->get_all_roles();
        $rolenames = array();
        foreach ($allroles as $id => $role) {
            $rolenames[$id] = $role->localname;
        }
        $mform->addElement('select', 'role', get_string('role'),
                array(0 => get_string('all')) + $rolenames);

                $allgroups = $manager->get_all_groups();
        $groupsmenu[0] = get_string('allparticipants');
        $groupsmenu[-1] = get_string('nogroup', 'enrol');
        foreach($allgroups as $gid => $unused) {
            $groupsmenu[$gid] = $allgroups[$gid]->name;
        }
        if (count($groupsmenu) > 1) {
            $mform->addElement('select', 'filtergroup', get_string('group'), $groupsmenu);
        }

                $mform->addElement('select', 'status', get_string('status'),
                array(-1 => get_string('all'),
                    ENROL_USER_ACTIVE => get_string('active'),
                    ENROL_USER_SUSPENDED => get_string('inactive')));

                                $group = array();
        $group[] = $mform->createElement('submit', 'submitbutton', get_string('filter'));
        $group[] = $mform->createElement('submit', 'resetbutton', get_string('reset'));
        $mform->addGroup($group, 'buttons', '', ' ', false);

                $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'newcourse', $this->_customdata['newcourse']);
        $mform->setType('newcourse', PARAM_BOOL);
    }
}
