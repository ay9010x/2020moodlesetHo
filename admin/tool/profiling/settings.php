<?php



defined('MOODLE_INTERNAL') || die;

if ((extension_loaded('xhprof') || extension_loaded('tideways')) && (!empty($CFG->profilingenabled) || !empty($CFG->earlyprofilingenabled))) {
    $ADMIN->add('development', new admin_externalpage('toolprofiling', get_string('pluginname', 'tool_profiling'),
            "$CFG->wwwroot/$CFG->admin/tool/profiling/index.php", 'moodle/site:config'));
}
