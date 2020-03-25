<?php


function xhprof_error($message) {
  error_log($message);
}


function xhprof_get_possible_metrics() {
 static $possible_metrics =
   array("wt" => array("Wall", "microsecs", "walltime"),
         "ut" => array("User", "microsecs", "user cpu time"),
         "st" => array("Sys", "microsecs", "system cpu time"),
         "cpu" => array("Cpu", "microsecs", "cpu time"),
         "mu" => array("MUse", "bytes", "memory usage"),
         "pmu" => array("PMUse", "bytes", "peak memory usage"),
         "samples" => array("Samples", "samples", "cpu time"));
 return $possible_metrics;
}


function init_metrics($xhprof_data, $rep_symbol, $sort, $diff_report = false) {
  global $stats;
  global $pc_stats;
  global $metrics;
  global $diff_mode;
  global $sortable_columns;
  global $sort_col;
  global $display_calls;

  $diff_mode = $diff_report;

  if (!empty($sort)) {
    if (array_key_exists($sort, $sortable_columns)) {
      $sort_col = $sort;
    } else {
      print("Invalid Sort Key $sort specified in URL");
    }
  }

      if (!isset($xhprof_data["main()"]["wt"])) {

    if ($sort_col == "wt") {
      $sort_col = "samples";
    }

                        $display_calls = false;
  } else {
    $display_calls = true;
  }

      if (!empty($rep_symbol)) {
    $sort_col = str_replace("excl_", "", $sort_col);
  }

  if ($display_calls) {
    $stats = array("fn", "ct", "Calls%");
  } else {
    $stats = array("fn");
  }

  $pc_stats = $stats;

  $possible_metrics = xhprof_get_possible_metrics($xhprof_data);
  foreach ($possible_metrics as $metric => $desc) {
    if (isset($xhprof_data["main()"][$metric])) {
      $metrics[] = $metric;
                  $stats[] = $metric;
      $stats[] = "I" . $desc[0] . "%";
      $stats[] = "excl_" . $metric;
      $stats[] = "E" . $desc[0] . "%";

                  $pc_stats[] = $metric;
      $pc_stats[] = "I" . $desc[0] . "%";
    }
  }
}


function xhprof_get_metrics($xhprof_data) {

    $possible_metrics = xhprof_get_possible_metrics();

      $metrics = array();
  foreach ($possible_metrics as $metric => $desc) {
    if (isset($xhprof_data["main()"][$metric])) {
      $metrics[] = $metric;
    }
  }

  return $metrics;
}


function xhprof_parse_parent_child($parent_child) {
  $ret = explode("==>", $parent_child);

    if (isset($ret[1])) {
    return $ret;
  }

  return array(null, $ret[0]);
}


function xhprof_build_parent_child_key($parent, $child) {
  if ($parent) {
    return $parent . "==>" . $child;
  } else {
    return $child;
  }
}



function xhprof_valid_run($run_id, $raw_data) {

  $main_info = $raw_data["main()"];
  if (empty($main_info)) {
    xhprof_error("XHProf: main() missing in raw data for Run ID: $run_id");
    return false;
  }

    if (isset($main_info["wt"])) {
    $metric = "wt";
  } else if (isset($main_info["samples"])) {
    $metric = "samples";
  } else {
    xhprof_error("XHProf: Wall Time information missing from Run ID: $run_id");
    return false;
  }

  foreach ($raw_data as $info) {
    $val = $info[$metric];

        if ($val < 0) {
      xhprof_error("XHProf: $metric should not be negative: Run ID $run_id"
                   . serialize($info));
      return false;
    }
    if ($val > (86400000000)) {
      xhprof_error("XHProf: $metric > 1 day found in Run ID: $run_id "
                   . serialize($info));
      return false;
    }
  }
  return true;
}



function xhprof_trim_run($raw_data, $functions_to_keep) {

    $function_map = array_fill_keys($functions_to_keep, 1);

      $function_map['main()'] = 1;

  $new_raw_data = array();
  foreach ($raw_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);

    if (isset($function_map[$parent]) || isset($function_map[$child])) {
      $new_raw_data[$parent_child] = $info;
    }
  }

  return $new_raw_data;
}


function xhprof_normalize_metrics($raw_data, $num_runs) {

  if (empty($raw_data) || ($num_runs == 0)) {
    return $raw_data;
  }

  $raw_data_total = array();

  if (isset($raw_data["==>main()"]) && isset($raw_data["main()"])) {
    xhprof_error("XHProf Error: both ==>main() and main() set in raw data...");
  }

  foreach ($raw_data as $parent_child => $info) {
    foreach ($info as $metric => $value) {
      $raw_data_total[$parent_child][$metric] = ($value / $num_runs);
    }
  }

  return $raw_data_total;
}



function xhprof_aggregate_runs($xhprof_runs_impl, $runs,
                               $wts, $source="phprof",
                               $use_script_name=false) {

  $raw_data_total = null;
  $raw_data       = null;
  $metrics        = array();

  $run_count = count($runs);
  $wts_count = count($wts);

  if (($run_count == 0) ||
      (($wts_count > 0) && ($run_count != $wts_count))) {
    return array('description' => 'Invalid input..',
                 'raw'  => null);
  }

  $bad_runs = array();
  foreach ($runs as $idx => $run_id) {

    $raw_data = $xhprof_runs_impl->get_run($run_id, $source, $description);

        if ($idx == 0) {
      foreach ($raw_data["main()"] as $metric => $val) {
        if ($metric != "pmu") {
                                        if (isset($val)) {
            $metrics[] = $metric;
          }
        }
      }
    }

    if (!xhprof_valid_run($run_id, $raw_data)) {
      $bad_runs[] = $run_id;
      continue;
    }

    if ($use_script_name) {
      $page = $description;

                                                      if ($page) {
        foreach ($raw_data["main()"] as $metric => $val) {
          $fake_edge[$metric] = $val;
          $new_main[$metric]  = $val + 0.00001;
        }
        $raw_data["main()"] = $new_main;
        $raw_data[xhprof_build_parent_child_key("main()",
                                                "__script::$page")]
          = $fake_edge;
      } else {
        $use_script_name = false;
      }
    }

        $wt = ($wts_count == 0) ? 1 : $wts[$idx];

        foreach ($raw_data as $parent_child => $info) {
      if ($use_script_name) {
                        if (substr($parent_child, 0, 9) == "main()==>") {
          $child = substr($parent_child, 9);
                    if (substr($child, 0, 10) != "__script::") {
            $parent_child = xhprof_build_parent_child_key("__script::$page",
                                                          $child);
          }
        }
      }

      if (!isset($raw_data_total[$parent_child])) {
        foreach ($metrics as $metric) {
          $raw_data_total[$parent_child][$metric] = ($wt * $info[$metric]);
        }
      } else {
        foreach ($metrics as $metric) {
          $raw_data_total[$parent_child][$metric] += ($wt * $info[$metric]);
        }
      }
    }
  }

  $runs_string = implode(",", $runs);

  if (isset($wts)) {
    $wts_string  = "in the ratio (" . implode(":", $wts) . ")";
    $normalization_count = array_sum($wts);
  } else {
    $wts_string = "";
    $normalization_count = $run_count;
  }

  $run_count = $run_count - count($bad_runs);

  $data['description'] = "Aggregated Report for $run_count runs: ".
                         "$runs_string $wts_string\n";
  $data['raw'] = xhprof_normalize_metrics($raw_data_total,
                                          $normalization_count);
  $data['bad_runs'] = $bad_runs;

  return $data;
}



function xhprof_compute_flat_info($raw_data, &$overall_totals) {

  global $display_calls;

  $metrics = xhprof_get_metrics($raw_data);

  $overall_totals = array("ct" => 0,
                           "wt" => 0,
                           "ut" => 0,
                           "st" => 0,
                           "cpu" => 0,
                           "mu" => 0,
                           "pmu" => 0,
                           "samples" => 0
                           );

    $symbol_tab = xhprof_compute_inclusive_times($raw_data);

  
  foreach ($metrics as $metric) {
    $overall_totals[$metric] = $symbol_tab["main()"][$metric];
  }

  
  foreach ($symbol_tab as $symbol => $info) {
    foreach ($metrics as $metric) {
      $symbol_tab[$symbol]["excl_" . $metric] = $symbol_tab[$symbol][$metric];
    }
    if ($display_calls) {
      
      $overall_totals["ct"] += $info["ct"];
    }
  }

  
  foreach ($raw_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);

    if ($parent) {
      foreach ($metrics as $metric) {
                if (isset($symbol_tab[$parent])) {
          $symbol_tab[$parent]["excl_" . $metric] -= $info[$metric];
        }
      }
    }
  }

  return $symbol_tab;
}


function xhprof_compute_diff($xhprof_data1, $xhprof_data2) {
  global $display_calls;

    $metrics = xhprof_get_metrics($xhprof_data2);

  $xhprof_delta = $xhprof_data2;

  foreach ($xhprof_data1 as $parent_child => $info) {

    if (!isset($xhprof_delta[$parent_child])) {

                  if ($display_calls) {
        $xhprof_delta[$parent_child] = array("ct" => 0);
      } else {
        $xhprof_delta[$parent_child] = array();
      }
      foreach ($metrics as $metric) {
        $xhprof_delta[$parent_child][$metric] = 0;
      }
    }

    if ($display_calls) {
      $xhprof_delta[$parent_child]["ct"] -= $info["ct"];
    }

    foreach ($metrics as $metric) {
      $xhprof_delta[$parent_child][$metric] -= $info[$metric];
    }
  }

  return $xhprof_delta;
}



function xhprof_compute_inclusive_times($raw_data) {
  global $display_calls;

  $metrics = xhprof_get_metrics($raw_data);

  $symbol_tab = array();

  
  foreach ($raw_data as $parent_child => $info) {

    list($parent, $child) = xhprof_parse_parent_child($parent_child);

    if ($parent == $child) {
      
      xhprof_error("Error in Raw Data: parent & child are both: $parent");
      return;
    }

    if (!isset($symbol_tab[$child])) {

      if ($display_calls) {
        $symbol_tab[$child] = array("ct" => $info["ct"]);
      } else {
        $symbol_tab[$child] = array();
      }
      foreach ($metrics as $metric) {
        $symbol_tab[$child][$metric] = $info[$metric];
      }
    } else {
      if ($display_calls) {
        
        $symbol_tab[$child]["ct"] += $info["ct"];
      }

      
      foreach ($metrics as $metric) {
        $symbol_tab[$child][$metric] += $info[$metric];
      }
    }
  }

  return $symbol_tab;
}



function xhprof_prune_run($raw_data, $prune_percent) {

  $main_info = $raw_data["main()"];
  if (empty($main_info)) {
    xhprof_error("XHProf: main() missing in raw data");
    return false;
  }

    if (isset($main_info["wt"])) {
    $prune_metric = "wt";
  } else if (isset($main_info["samples"])) {
    $prune_metric = "samples";
  } else {
    xhprof_error("XHProf: for main() we must have either wt "
                 ."or samples attribute set");
    return false;
  }

    $metrics = array();
  foreach ($main_info as $metric => $val) {
    if (isset($val)) {
      $metrics[] = $metric;
    }
  }

  $prune_threshold = (($main_info[$prune_metric] * $prune_percent) / 100.0);

  init_metrics($raw_data, null, null, false);
  $flat_info = xhprof_compute_inclusive_times($raw_data);

  foreach ($raw_data as $parent_child => $info) {

    list($parent, $child) = xhprof_parse_parent_child($parent_child);

        if ($flat_info[$child][$prune_metric] < $prune_threshold) {
      unset($raw_data[$parent_child]);     } else if ($parent &&
               ($parent != "__pruned__()") &&
               ($flat_info[$parent][$prune_metric] < $prune_threshold)) {

                              $pruned_edge = xhprof_build_parent_child_key("__pruned__()", $child);

      if (isset($raw_data[$pruned_edge])) {
        foreach ($metrics as $metric) {
          $raw_data[$pruned_edge][$metric]+=$raw_data[$parent_child][$metric];
        }
      } else {
        $raw_data[$pruned_edge] = $raw_data[$parent_child];
      }

      unset($raw_data[$parent_child]);     }
  }

  return $raw_data;
}



function xhprof_array_set($arr, $k, $v) {
  $arr[$k] = $v;
  return $arr;
}


function xhprof_array_unset($arr, $k) {
  unset($arr[$k]);
  return $arr;
}


define('XHPROF_STRING_PARAM', 1);
define('XHPROF_UINT_PARAM',   2);
define('XHPROF_FLOAT_PARAM',  3);
define('XHPROF_BOOL_PARAM',   4);



function xhprof_get_param_helper($param) {
  $val = null;
  if (isset($_GET[$param]))
    $val = $_GET[$param];
  else if (isset($_POST[$param])) {
    $val = $_POST[$param];
  }
  return $val;
}


function xhprof_get_string_param($param, $default = '') {
  $val = xhprof_get_param_helper($param);

  if ($val === null)
    return $default;

  return $val;
}


function xhprof_get_uint_param($param, $default = 0) {
  $val = xhprof_get_param_helper($param);

  if ($val === null)
    $val = $default;

    $val = trim($val);

    if (ctype_digit($val)) {
    return $val;
  }

  xhprof_error("$param is $val. It must be an unsigned integer.");
  return null;
}



function xhprof_get_float_param($param, $default = 0) {
  $val = xhprof_get_param_helper($param);

  if ($val === null)
    $val = $default;

    $val = trim($val);

    if (true)     return (float)$val;

  xhprof_error("$param is $val. It must be a float.");
  return null;
}


function xhprof_get_bool_param($param, $default = false) {
  $val = xhprof_get_param_helper($param);

  if ($val === null)
    $val = $default;

    $val = trim($val);

  switch (strtolower($val)) {
  case '0':
  case '1':
    $val = (bool)$val;
    break;
  case 'true':
  case 'on':
  case 'yes':
    $val = true;
    break;
  case 'false':
  case 'off':
  case 'no':
    $val = false;
    break;
  default:
    xhprof_error("$param is $val. It must be a valid boolean string.");
    return null;
  }

  return $val;

}


function xhprof_param_init($params) {
  
  foreach ($params as $k => $v) {
    switch ($v[0]) {
    case XHPROF_STRING_PARAM:
      $p = xhprof_get_string_param($k, $v[1]);
      break;
    case XHPROF_UINT_PARAM:
      $p = xhprof_get_uint_param($k, $v[1]);
      break;
    case XHPROF_FLOAT_PARAM:
      $p = xhprof_get_float_param($k, $v[1]);
      break;
    case XHPROF_BOOL_PARAM:
      $p = xhprof_get_bool_param($k, $v[1]);
      break;
    default:
      xhprof_error("Invalid param type passed to xhprof_param_init: "
                   . $v[0]);
      exit();
    }

        $GLOBALS[$k] = $p;
  }
}



function xhprof_get_matching_functions($q, $xhprof_data) {

  $matches = array();

  foreach ($xhprof_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);
    if (stripos($parent, $q) !== false) {
      $matches[$parent] = 1;
    }
    if (stripos($child, $q) !== false) {
      $matches[$child] = 1;
    }
  }

  $res = array_keys($matches);

    asort($res);

  return ($res);
}

