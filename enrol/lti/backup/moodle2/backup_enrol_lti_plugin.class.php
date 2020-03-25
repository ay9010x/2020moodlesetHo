<?php



defined('MOODLE_INTERNAL') || die();


class backup_enrol_lti_plugin extends backup_enrol_plugin {

    
    public function define_enrol_plugin_structure() {
                $plugin = $this->get_plugin_element();

                $tool = new backup_nested_element('tool', array('id'), array(
            'enrolid', 'contextid', 'institution', 'lang', 'timezone', 'maxenrolled', 'maildisplay', 'city',
            'country', 'gradesync', 'gradesynccompletion', 'membersync', 'membersyncmode',  'roleinstructor',
            'rolelearner', 'secret', 'timecreated', 'timemodified'));

        $users = new backup_nested_element('users');

        $user = new backup_nested_element('user', array('id'), array(
            'userid', 'toolid', 'serviceurl', 'sourceid', 'consumerkey', 'consumersecret', 'membershipurl',
            'membershipsid'));

                $plugin->add_child($tool);
        $tool->add_child($users);
        $users->add_child($user);

                $tool->set_source_table('enrol_lti_tools',
            array('enrolid' => backup::VAR_PARENTID));

                if ($this->task->get_setting_value('users')) {
            $user->set_source_table('enrol_lti_users', array('toolid' => backup::VAR_PARENTID));
        }
    }
}
