<?php




defined('MOODLE_INTERNAL') || die();

$options = array();
for ($i = 9; $i >= 1; $i = $i-2) {
    $options[$i] = get_string('comparisonlevel' . $i, 'workshopeval_best');
}

$settings->add(new admin_setting_configselect('workshopeval_best/comparison', get_string('comparison', 'workshopeval_best'),
                    get_string('configcomparison', 'workshopeval_best'), 5, $options));
