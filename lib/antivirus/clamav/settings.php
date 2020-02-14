<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configexecutable('antivirus_clamav/pathtoclam',
            new lang_string('pathtoclam', 'antivirus_clamav'), new lang_string('configpathtoclam', 'antivirus_clamav'), ''));
    $settings->add(new admin_setting_configdirectory('antivirus_clamav/quarantinedir',
            new lang_string('quarantinedir', 'antivirus_clamav'), new lang_string('configquarantinedir', 'antivirus_clamav'), ''));
    $options = array(
        'donothing' => new lang_string('configclamdonothing', 'antivirus_clamav'),
        'actlikevirus' => new lang_string('configclamactlikevirus', 'antivirus_clamav'),
    );
    $settings->add(new admin_setting_configselect('antivirus_clamav/clamfailureonupload',
            new lang_string('clamfailureonupload', 'antivirus_clamav'),
            new lang_string('configclamfailureonupload', 'antivirus_clamav'), 'donothing', $options));
}