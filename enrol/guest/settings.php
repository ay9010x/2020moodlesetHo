<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_guest_settings', '', get_string('pluginname_desc', 'enrol_guest')));

    $settings->add(new admin_setting_configcheckbox('enrol_guest/requirepassword',
        get_string('requirepassword', 'enrol_guest'), get_string('requirepassword_desc', 'enrol_guest'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_guest/usepasswordpolicy',
        get_string('usepasswordpolicy', 'enrol_guest'), get_string('usepasswordpolicy_desc', 'enrol_guest'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_guest/showhint',
        get_string('showhint', 'enrol_guest'), get_string('showhint_desc', 'enrol_guest'), 0));


        $settings->add(new admin_setting_heading('enrol_guest_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $settings->add(new admin_setting_configcheckbox('enrol_guest/defaultenrol',
        get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect_with_advanced('enrol_guest/status',
        get_string('status', 'enrol_guest'), get_string('status_desc', 'enrol_guest'),
        array('value'=>ENROL_INSTANCE_DISABLED, 'adv'=>false), $options));
}

