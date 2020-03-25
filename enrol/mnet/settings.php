<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_mnet_settings', '', get_string('pluginname_desc', 'enrol_mnet')));

        if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect_with_advanced('enrol_mnet/roleid',
            get_string('defaultrole', 'role'), '',
            array('value'=>$student->id, 'adv'=>true), $options));
    }
}
