<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $numberofsections = array();

    for ($i = 1; $i < 53; $i++){
        $numberofsections[$i] = $i;
    }
    $increments = array();

    for ($i = 1; $i < 11; $i++){
        $increments[$i] = $i;
    }

    $selected = array(1 => array(22,2),
                      2 => array(40,5));

    for($i = 1; $i < 3; $i++){
        $settings->add(new admin_setting_configselect('block_section_links/numsections'.$i, get_string('numsections'.$i, 'block_section_links'),
                            get_string('numsections'.$i.'_help', 'block_section_links'),
                            $selected[$i][0], $numberofsections));

        $settings->add(new admin_setting_configselect('block_section_links/incby'.$i, get_string('incby'.$i, 'block_section_links'),
                            get_string('incby'.$i.'_help', 'block_section_links'),
                            $selected[$i][1], $increments));
    }
}