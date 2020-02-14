<?php


defined('MOODLE_INTERNAL') || die();

if (get_config('core_competency', 'enabled')) {

    $parentname = 'competencies';

        $temp = new admin_externalpage(
        'toollpmigrateframeworks',
        get_string('migrateframeworks', 'tool_lpmigrate'),
        new moodle_url('/admin/tool/lpmigrate/frameworks.php'),
        array('tool/lpmigrate:frameworksmigrate')
    );
    $ADMIN->add($parentname, $temp);

}
