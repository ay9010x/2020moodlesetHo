<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

$settings->add(new admin_setting_configselect('workshopallocation_random/numofreviews',
        get_string('numofreviews', 'workshopallocation_random'),
        get_string('confignumofreviews', 'workshopallocation_random'), 5,
                workshop_random_allocator::available_numofreviews_list()));
