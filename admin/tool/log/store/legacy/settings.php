<?php



defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings->add(new admin_setting_configcheckbox('logstore_legacy/loglegacy',
        new lang_string('loglegacy', 'logstore_legacy'),
        new lang_string('loglegacy_help', 'logstore_legacy'), 0));

    $settings->add(new admin_setting_configcheckbox('logguests',
        new lang_string('logguests', 'admin'),
        new lang_string('logguests_help', 'admin'), 1));

    $options = array(0    => new lang_string('neverdeletelogs'),
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

    $settings->add(new admin_setting_configselect('loglifetime',
        new lang_string('loglifetime', 'admin'),
        new lang_string('configloglifetime', 'admin'), 0, $options));
}
