<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once(dirname(__FILE__).'/lib.php');

        $options = array(
          0 => get_string('donotusepaging', 'attendance'),
         25 => 25,
         50 => 50,
         75 => 75,
         100 => 100,
         250 => 250,
         500 => 500,
         1000 => 1000,
    );

    $settings->add(new admin_setting_configselect('attendance/resultsperpage',
        get_string('resultsperpage', 'attendance'), get_string('resultsperpage_desc', 'attendance'), 25, $options));
}
