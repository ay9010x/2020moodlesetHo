<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_heading('imscpmodeditdefaults',
                                             get_string('modeditdefaults', 'admin'),
                                             get_string('condifmodeditdefaults', 'admin')));
    $options = array('-1' => get_string('all'), '0' => get_string('no'),
                     '1' => '1', '2' => '2', '5' => '5', '10' => '10', '20' => '20');
    $settings->add(new admin_setting_configselect_with_advanced('imscp/keepold',
        get_string('keepold', 'imscp'), get_string('keepoldexplain', 'imscp'),
        array('value' => 1, 'adv' => false), $options));
}
