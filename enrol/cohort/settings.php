<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_cohort_settings', '', get_string('pluginname_desc', 'enrol_cohort')));


        if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_cohort/roleid',
            get_string('defaultrole', 'role'), '', $student->id, $options));

        $options = array(
            ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
            ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));
        $settings->add(new admin_setting_configselect('enrol_cohort/unenrolaction', get_string('extremovedaction', 'enrol'), get_string('extremovedaction_help', 'enrol'), ENROL_EXT_REMOVED_UNENROL, $options));
    }
}
