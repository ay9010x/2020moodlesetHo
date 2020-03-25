<?php


defined('MOODLE_INTERNAL') || die;
$temp = new admin_externalpage(
    'toolmoodleset',
    get_string('pluginname', 'tool_moodleset'),
    new moodle_url('/admin/tool/moodleset/index.php')
);
$ADMIN->add('development', $temp);
