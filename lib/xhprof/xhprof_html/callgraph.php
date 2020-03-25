<?php



require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir . '/xhprof/xhprof_moodle.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
\core\session\manager::write_close();

$GLOBALS['XHPROF_LIB_ROOT'] = dirname(__FILE__) . '/../xhprof_lib';

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';

ini_set('max_execution_time', 100);

$params = array(                'run' => array(XHPROF_STRING_PARAM, ''),

                                'source' => array(XHPROF_STRING_PARAM, 'xhprof'),

                                                'func' => array(XHPROF_STRING_PARAM, ''),

                                'type' => array(XHPROF_STRING_PARAM, 'png'),

                                                                'threshold' => array(XHPROF_FLOAT_PARAM, 0.01),

                                'critical' => array(XHPROF_BOOL_PARAM, true),

                                'run1' => array(XHPROF_STRING_PARAM, ''),

                                'run2' => array(XHPROF_STRING_PARAM, '')
                );

xhprof_param_init($params);

if ($threshold < 0 || $threshold > 1) {
  $threshold = $params['threshold'][1];
}

if (!array_key_exists($type, $xhprof_legal_image_types)) {
  $type = $params['type'][1]; }

$xhprof_runs_impl = new moodle_xhprofrun();

if (!empty($run)) {
    xhprof_render_image($xhprof_runs_impl, $run, $type,
                      $threshold, $func, $source, $critical);
} else {
    xhprof_render_diff_image($xhprof_runs_impl, $run1, $run2,
                           $type, $threshold, $source);
}
