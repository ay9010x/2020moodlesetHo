<?php


defined('MOODLE_INTERNAL') || die();

$parentname = 'competencies';

if (get_config('core_competency', 'enabled')) {

        $temp = new admin_externalpage(
        'toollpcompetencies',
        get_string('competencyframeworks', 'tool_lp'),
        new moodle_url('/admin/tool/lp/competencyframeworks.php', array('pagecontextid' => context_system::instance()->id)),
        array('moodle/competency:competencymanage')
    );
    $ADMIN->add($parentname, $temp);

        $temp = new admin_externalpage(
        'toollplearningplans',
        get_string('templates', 'tool_lp'),
        new moodle_url('/admin/tool/lp/learningplans.php', array('pagecontextid' => context_system::instance()->id)),
        array('moodle/competency:templatemanage')
    );
    $ADMIN->add($parentname, $temp);
}
