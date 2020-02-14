<?php



require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir . '/xhprof/xhprof_moodle.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
\core\session\manager::write_close();

$GLOBALS['XHPROF_LIB_ROOT'] = dirname(__FILE__) . '/../xhprof_lib';

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';

$xhprof_runs_impl = new moodle_xhprofrun();

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/typeahead_common.php';
