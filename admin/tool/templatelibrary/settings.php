<?php


defined('MOODLE_INTERNAL') || die;
$temp = new admin_externalpage(
    'tooltemplatelibrary',
    get_string('pluginname', 'tool_templatelibrary'),
    new moodle_url('/admin/tool/templatelibrary/index.php')
);
$ADMIN->add('development', $temp);
