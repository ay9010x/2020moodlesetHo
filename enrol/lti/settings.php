<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('enrol_lti_settings', '', get_string('pluginname_desc', 'enrol_lti')));

    if (!is_enabled_auth('lti')) {
        $notify = new \core\output\notification(get_string('authltimustbeenabled', 'enrol_lti'),
            \core\output\notification::NOTIFY_WARNING);
        $settings->add(new admin_setting_heading('enrol_lti_enable_auth_lti', '', $OUTPUT->render($notify)));
    }

    if (empty($CFG->allowframembedding)) {
        $notify = new \core\output\notification(get_string('allowframeembedding', 'enrol_lti'),
            \core\output\notification::NOTIFY_WARNING);
        $settings->add(new admin_setting_heading('enrol_lti_enable_embedding', '', $OUTPUT->render($notify)));
    }

    $settings->add(new admin_setting_heading('enrol_lti_user_default_values',
        get_string('userdefaultvalues', 'enrol_lti'), ''));

    $choices = array(0 => get_string('emaildisplayno'),
                     1 => get_string('emaildisplayyes'),
                     2 => get_string('emaildisplaycourse'));
    $maildisplay = isset($CFG->defaultpreference_maildisplay) ? $CFG->defaultpreference_maildisplay : 2;
    $settings->add(new admin_setting_configselect('enrol_lti/emaildisplay', get_string('emaildisplay'), '',
        $maildisplay, $choices));

    $city = '';
    if (!empty($CFG->defaultcity)) {
        $city = $CFG->defaultcity;
    }
    $settings->add(new admin_setting_configtext('enrol_lti/city', get_string('city'), '', $city));

    $country = '';
    if (!empty($CFG->country)) {
        $country = $CFG->country;
    }
    $countries = array('' => get_string('selectacountry') . '...') + get_string_manager()->get_list_of_countries();
    $settings->add(new admin_setting_configselect('enrol_lti/country', get_string('selectacountry'), '', $country,
        $countries));

    $settings->add(new admin_setting_configselect('enrol_lti/timezone', get_string('timezone'), '', 99,
        core_date::get_list_of_timezones(null, true)));

    $settings->add(new admin_setting_configselect('enrol_lti/lang', get_string('preferredlanguage'), '', $CFG->lang,
        get_string_manager()->get_list_of_translations()));

    $settings->add(new admin_setting_configtext('enrol_lti/institution', get_string('institution'), '', ''));
}
