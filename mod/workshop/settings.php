<?php




defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/workshop/locallib.php');

    $grades = workshop::available_maxgrades_list();

    $settings->add(new admin_setting_configselect('workshop/grade', get_string('submissiongrade', 'workshop'),
                        get_string('configgrade', 'workshop'), 80, $grades));

    $settings->add(new admin_setting_configselect('workshop/gradinggrade', get_string('gradinggrade', 'workshop'),
                        get_string('configgradinggrade', 'workshop'), 20, $grades));

    $options = array();
    for ($i = 5; $i >= 0; $i--) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('workshop/gradedecimals', get_string('gradedecimals', 'workshop'),
                        get_string('configgradedecimals', 'workshop'), 0, $options));

    if (isset($CFG->maxbytes)) {
        $maxbytes = get_config('workshop', 'maxbytes');
        $options = get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes);
        $settings->add(new admin_setting_configselect('workshop/maxbytes', get_string('maxbytes', 'workshop'),
                            get_string('configmaxbytes', 'workshop'), 0, $options));
    }

    $settings->add(new admin_setting_configselect('workshop/strategy', get_string('strategy', 'workshop'),
                        get_string('configstrategy', 'workshop'), 'accumulative', workshop::available_strategies_list()));

    $options = workshop::available_example_modes_list();
    $settings->add(new admin_setting_configselect('workshop/examplesmode', get_string('examplesmode', 'workshop'),
                        get_string('configexamplesmode', 'workshop'), workshop::EXAMPLES_VOLUNTARY, $options));

        $allocators = core_component::get_plugin_list('workshopallocation');
    foreach ($allocators as $allocator => $path) {
        if (file_exists($settingsfile = $path . '/settings.php')) {
            $settings->add(new admin_setting_heading('workshopallocationsetting'.$allocator,
                    get_string('allocation', 'workshop') . ' - ' . get_string('pluginname', 'workshopallocation_' . $allocator), ''));
            include($settingsfile);
        }
    }

        $strategies = core_component::get_plugin_list('workshopform');
    foreach ($strategies as $strategy => $path) {
        if (file_exists($settingsfile = $path . '/settings.php')) {
            $settings->add(new admin_setting_heading('workshopformsetting'.$strategy,
                    get_string('strategy', 'workshop') . ' - ' . get_string('pluginname', 'workshopform_' . $strategy), ''));
            include($settingsfile);
        }
    }

        $evaluations = core_component::get_plugin_list('workshopeval');
    foreach ($evaluations as $evaluation => $path) {
        if (file_exists($settingsfile = $path . '/settings.php')) {
            $settings->add(new admin_setting_heading('workshopevalsetting'.$evaluation,
                    get_string('evaluation', 'workshop') . ' - ' . get_string('pluginname', 'workshopeval_' . $evaluation), ''));
            include($settingsfile);
        }
    }

}
