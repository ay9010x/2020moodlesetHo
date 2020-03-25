<?php



defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings->add(new admin_setting_configcheckbox('logstore_standard/logguests',
        new lang_string('logguests', 'core_admin'),
        new lang_string('logguests_help', 'core_admin'), 1));

    $options = array(
        0    => new lang_string('neverdeletelogs'),
        1000 => new lang_string('numdays', '', 1000),
        365  => new lang_string('numdays', '', 365),
        180  => new lang_string('numdays', '', 180),
        150  => new lang_string('numdays', '', 150),
        120  => new lang_string('numdays', '', 120),
        90   => new lang_string('numdays', '', 90),
        60   => new lang_string('numdays', '', 60),
        35   => new lang_string('numdays', '', 35),
        10   => new lang_string('numdays', '', 10),
        5    => new lang_string('numdays', '', 5),
        2    => new lang_string('numdays', '', 2));
    $settings->add(new admin_setting_configselect('logstore_standard/loglifetime',
        new lang_string('loglifetime', 'core_admin'),
        new lang_string('configloglifetime', 'core_admin'), 0, $options));

    $settings->add(new admin_setting_configtext('logstore_standard/buffersize',
        get_string('buffersize', 'logstore_standard'),
        '', '50', PARAM_INT));
}
