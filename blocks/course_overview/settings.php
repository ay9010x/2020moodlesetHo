<?php


defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_course_overview/defaultmaxcourses', new lang_string('defaultmaxcourses', 'block_course_overview'),
        new lang_string('defaultmaxcoursesdesc', 'block_course_overview'), 10, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('block_course_overview/forcedefaultmaxcourses', new lang_string('forcedefaultmaxcourses', 'block_course_overview'),
        new lang_string('forcedefaultmaxcoursesdesc', 'block_course_overview'), 1, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('block_course_overview/showchildren', new lang_string('showchildren', 'block_course_overview'),
        new lang_string('showchildrendesc', 'block_course_overview'), 1, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('block_course_overview/showwelcomearea', new lang_string('showwelcomearea', 'block_course_overview'),
        new lang_string('showwelcomeareadesc', 'block_course_overview'), 1, PARAM_INT));
    $showcategories = array(
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE => new lang_string('none', 'block_course_overview'),
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_ONLY_PARENT_NAME => new lang_string('onlyparentname', 'block_course_overview'),
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH => new lang_string('fullpath', 'block_course_overview')
    );
    $settings->add(new admin_setting_configselect('block_course_overview/showcategories', new lang_string('showcategories', 'block_course_overview'),
        new lang_string('showcategoriesdesc', 'block_course_overview'), BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE, $showcategories));
}
