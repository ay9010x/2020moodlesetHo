<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage('toolspamcleaner', get_string('pluginname', 'tool_spamcleaner'), "$CFG->wwwroot/$CFG->admin/tool/spamcleaner/index.php", 'moodle/site:config'));
}

