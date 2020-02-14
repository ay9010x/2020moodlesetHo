<?php




require_once $GLOBALS['XHPROF_LIB_ROOT'].'/utils/xhprof_lib.php';

$params = array('q'          => array(XHPROF_STRING_PARAM, ''),
                'run'        => array(XHPROF_STRING_PARAM, ''),
                'run1'       => array(XHPROF_STRING_PARAM, ''),
                'run2'       => array(XHPROF_STRING_PARAM, ''),
                'source'     => array(XHPROF_STRING_PARAM, 'xhprof'),
                );

xhprof_param_init($params);

if (!empty($run)) {

    $raw_data = $xhprof_runs_impl->get_run($run, $source, $desc_unused);
  $functions = xhprof_get_matching_functions($q, $raw_data);

} else if (!empty($run1) && !empty($run2)) {

    $raw_data = $xhprof_runs_impl->get_run($run1, $source, $desc_unused);
  $functions1 = xhprof_get_matching_functions($q, $raw_data);

  $raw_data = $xhprof_runs_impl->get_run($run2, $source, $desc_unused);
  $functions2 = xhprof_get_matching_functions($q, $raw_data);


  $functions = array_unique(array_merge($functions1, $functions2));
  asort($functions);
} else {
  xhprof_error("no valid runs specified to typeahead endpoint");
  $functions = array();
}

if (in_array($q, $functions)) {
  $old_functions = $functions;

  $functions = array($q);
  foreach ($old_functions as $f) {
        if ($f != $q) {
      $functions[] = $f;
    }
  }
}

foreach ($functions as $f) {
  echo $f."\n";
}

