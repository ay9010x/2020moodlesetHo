<?php



defined('MOODLE_INTERNAL') || die();

if (has_capability('moodle/site:config', $systemcontext)) {

    $parentname = 'competencies';

        $settings = new admin_settingpage('competencysettings', new lang_string('competenciessettings', 'core_competency'),
        'moodle/site:config', false);
    $ADMIN->add($parentname, $settings);

        if ($ADMIN->fulltree) {
        $setting = new admin_setting_configcheckbox('core_competency/enabled',
            new lang_string('enablecompetencies', 'core_competency'),
            new lang_string('enablecompetencies_desc', 'core_competency'), 1);
        $settings->add($setting);

        $setting = new admin_setting_configcheckbox('core_competency/pushcourseratingstouserplans',
            new lang_string('pushcourseratingstouserplans', 'core_competency'),
            new lang_string('pushcourseratingstouserplans_desc', 'core_competency'), 1);
        $settings->add($setting);
    }

}
