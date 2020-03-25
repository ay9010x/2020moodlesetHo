<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_paypal_settings', '', get_string('pluginname_desc', 'enrol_paypal')));

    $settings->add(new admin_setting_configtext('enrol_paypal/paypalbusiness', get_string('businessemail', 'enrol_paypal'), get_string('businessemail_desc', 'enrol_paypal'), '', PARAM_EMAIL));

    $settings->add(new admin_setting_configcheckbox('enrol_paypal/mailstudents', get_string('mailstudents', 'enrol_paypal'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_paypal/mailteachers', get_string('mailteachers', 'enrol_paypal'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_paypal/mailadmins', get_string('mailadmins', 'enrol_paypal'), '', 0));

            $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_paypal/expiredaction', get_string('expiredaction', 'enrol_paypal'), get_string('expiredaction_help', 'enrol_paypal'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

        $settings->add(new admin_setting_heading('enrol_paypal_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_paypal/status',
        get_string('status', 'enrol_paypal'), get_string('status_desc', 'enrol_paypal'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_paypal/cost', get_string('cost', 'enrol_paypal'), '', 0, PARAM_FLOAT, 4));

    $paypalcurrencies = enrol_get_plugin('paypal')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_paypal/currency', get_string('currency', 'enrol_paypal'), '', 'USD', $paypalcurrencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_paypal/roleid',
            get_string('defaultrole', 'enrol_paypal'), get_string('defaultrole_desc', 'enrol_paypal'), $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_paypal/enrolperiod',
        get_string('enrolperiod', 'enrol_paypal'), get_string('enrolperiod_desc', 'enrol_paypal'), 0));
}
