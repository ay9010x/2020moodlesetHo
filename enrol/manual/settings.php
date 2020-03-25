<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_manual_settings', '', get_string('pluginname_desc', 'enrol_manual')));

            $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_manual/expiredaction', get_string('expiredaction', 'enrol_manual'), get_string('expiredaction_help', 'enrol_manual'), ENROL_EXT_REMOVED_KEEP, $options));

    $options = array();
    for ($i=0; $i<24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('enrol_manual/expirynotifyhour', get_string('expirynotifyhour', 'core_enrol'), '', 6, $options));


        $settings->add(new admin_setting_heading('enrol_manual_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $settings->add(new admin_setting_configcheckbox('enrol_manual/defaultenrol',
        get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_manual/status',
        get_string('status', 'enrol_manual'), get_string('status_desc', 'enrol_manual'), ENROL_INSTANCE_ENABLED, $options));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_manual/roleid',
            get_string('defaultrole', 'role'), '', $student->id, $options));
    }

    $options = array(2 => get_string('coursestart'), 3 => get_string('today'), 4 => get_string('now', 'enrol_manual'));
    $settings->add(
        new admin_setting_configselect('enrol_manual/enrolstart', get_string('defaultstart', 'enrol_manual'), '', 4, $options)
    );

    $settings->add(new admin_setting_configduration('enrol_manual/enrolperiod',
        get_string('defaultperiod', 'enrol_manual'), get_string('defaultperiod_desc', 'enrol_manual'), 0));

    $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
    $settings->add(new admin_setting_configselect('enrol_manual/expirynotify',
        get_string('expirynotify', 'core_enrol'), get_string('expirynotify_help', 'core_enrol'), 0, $options));

    $settings->add(new admin_setting_configduration('enrol_manual/expirythreshold',
        get_string('expirythreshold', 'core_enrol'), get_string('expirythreshold_help', 'core_enrol'), 86400, 86400));

}
