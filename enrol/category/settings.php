<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_category_settings', '', get_string('pluginname_desc', 'enrol_category')));


    
}
