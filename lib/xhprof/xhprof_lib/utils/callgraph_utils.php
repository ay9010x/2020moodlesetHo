<?php



$xhprof_legal_image_types = array(
    "jpg" => 1,
    "gif" => 1,
    "png" => 1,
    "svg" => 1,     "ps"  => 1,
    );


function xhprof_http_header($name, $value) {

  if (!$name) {
    xhprof_error('http_header usage');
    return null;
  }

  if (!is_string($value)) {
    xhprof_error('http_header value not a string');
  }

  header($name.': '.$value, true);
}


function xhprof_generate_mime_header($type, $length) {
  switch ($type) {
    case 'jpg':
      $mime = 'image/jpeg';
      break;
    case 'gif':
      $mime = 'image/gif';
      break;
    case 'png':
      $mime = 'image/png';
      break;
    case 'svg':
      $mime = 'image/svg+xml';       break;
    case 'ps':
      $mime = 'application/postscript';
    default:
      $mime = false;
  }

  if ($mime) {
    xhprof_http_header('Content-type', $mime);
    xhprof_http_header('Content-length', (string)$length);
  }
}


function xhprof_generate_image_by_dot($dot_script, $type) {
  $descriptorspec = array(
              0 => array("pipe", "r"),
              1 => array("pipe", "w"),
              2 => array("pipe", "w")
       );

      global $CFG;
  $cmd = (!empty($CFG->pathtodot) ? $CFG->pathtodot : 'dot') . ' -T' . $type;
  
  $process = proc_open($cmd, $descriptorspec, $pipes, "/tmp", array());
  if (is_resource($process)) {
    fwrite($pipes[0], $dot_script);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);

    $err = stream_get_contents($pipes[2]);
    if (!empty($err)) {
      print "failed to execute cmd: \"$cmd\". stderr: `$err'\n";
      exit;
    }

    fclose($pipes[2]);
    fclose($pipes[1]);
    proc_close($process);
    return $output;
  }
  print "failed to execute cmd \"$cmd\"";
  exit();
}


function xhprof_get_children_table($raw_data) {
  $children_table = array();
  foreach ($raw_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);
    if (!isset($children_table[$parent])) {
      $children_table[$parent] = array($child);
    } else {
      $children_table[$parent][] = $child;
    }
  }
  return $children_table;
}


function xhprof_generate_dot_script($raw_data, $threshold, $source, $page,
                                    $func, $critical_path, $right=null,
                                    $left=null) {

  $max_width = 5;
  $max_height = 3.5;
  $max_fontsize = 35;
  $max_sizing_ratio = 20;

  $totals;

  if ($left === null) {
      }
  $sym_table = xhprof_compute_flat_info($raw_data, $totals);

  if ($critical_path) {
    $children_table = xhprof_get_children_table($raw_data);
    $node = "main()";
    $path = array();
    $path_edges = array();
    $visited = array();
    while ($node) {
      $visited[$node] = true;
      if (isset($children_table[$node])) {
        $max_child = null;
        foreach ($children_table[$node] as $child) {

          if (isset($visited[$child])) {
            continue;
          }
          if ($max_child === null ||
            abs($raw_data[xhprof_build_parent_child_key($node,
                                                        $child)]["wt"]) >
            abs($raw_data[xhprof_build_parent_child_key($node,
                                                        $max_child)]["wt"])) {
            $max_child = $child;
          }
        }
        if ($max_child !== null) {
          $path[$max_child] = true;
          $path_edges[xhprof_build_parent_child_key($node, $max_child)] = true;
        }
        $node = $max_child;
      } else {
        $node = null;
      }
    }
  }

   if ($source == "bm" && array_key_exists("main()", $sym_table)) {
    $total_times = $sym_table["main()"]["ct"];
    $remove_funcs = array("main()",
                          "hotprofiler_disable",
                          "call_user_func_array",
                          "xhprof_disable");

    foreach ($remove_funcs as $cur_del_func) {
      if (array_key_exists($cur_del_func, $sym_table) &&
          $sym_table[$cur_del_func]["ct"] == $total_times) {
        unset($sym_table[$cur_del_func]);
      }
    }
  }

    if (!empty($func)) {
    $interested_funcs = array();
    foreach ($raw_data as $parent_child => $info) {
      list($parent, $child) = xhprof_parse_parent_child($parent_child);
      if ($parent == $func || $child == $func) {
        $interested_funcs[$parent] = 1;
        $interested_funcs[$child] = 1;
      }
    }
    foreach ($sym_table as $symbol => $info) {
      if (!array_key_exists($symbol, $interested_funcs)) {
        unset($sym_table[$symbol]);
      }
    }
  }

  $result = "digraph call_graph {\n";

          $cur_id = 0; $max_wt = 0;
  foreach ($sym_table as $symbol => $info) {
    if (empty($func) && abs($info["wt"] / $totals["wt"]) < $threshold) {
      unset($sym_table[$symbol]);
      continue;
    }
    if ($max_wt == 0 || $max_wt < abs($info["excl_wt"])) {
      $max_wt = abs($info["excl_wt"]);
    }
    $sym_table[$symbol]["id"] = $cur_id;
    $cur_id ++;
  }

    foreach ($sym_table as $symbol => $info) {
    if ($info["excl_wt"] == 0) {
      $sizing_factor = $max_sizing_ratio;
    } else {
      $sizing_factor = $max_wt / abs($info["excl_wt"]) ;
      if ($sizing_factor > $max_sizing_ratio) {
        $sizing_factor = $max_sizing_ratio;
      }
    }
    $fillcolor = (($sizing_factor < 1.5) ?
                  ", style=filled, fillcolor=red" : "");

    if ($critical_path) {
            if (!$fillcolor && array_key_exists($symbol, $path)) {
        $fillcolor = ", style=filled, fillcolor=yellow";
      }
    }

    $fontsize = ", fontsize="
               .(int)($max_fontsize / (($sizing_factor - 1) / 10 + 1));

    $width = ", width=".sprintf("%.1f", $max_width / $sizing_factor);
    $height = ", height=".sprintf("%.1f", $max_height / $sizing_factor);

    if ($symbol == "main()") {
      $shape = "octagon";
      $name = "Total: ".($totals["wt"] / 1000.0)." ms\\n";
      $name .= addslashes(isset($page) ? $page : $symbol);
    } else {
      $shape = "box";
      $name = addslashes($symbol)."\\nInc: ". sprintf("%.3f",$info["wt"] / 1000) .
              " ms (" . sprintf("%.1f%%", 100 * $info["wt"] / $totals["wt"]).")";
    }
    if ($left === null) {
      $label = ", label=\"".$name."\\nExcl: "
               .(sprintf("%.3f",$info["excl_wt"] / 1000.0))." ms ("
               .sprintf("%.1f%%", 100 * $info["excl_wt"] / $totals["wt"])
               . ")\\n".$info["ct"]." total calls\"";
    } else {
      if (isset($left[$symbol]) && isset($right[$symbol])) {
         $label = ", label=\"".addslashes($symbol).
                  "\\nInc: ".(sprintf("%.3f",$left[$symbol]["wt"] / 1000.0))
                  ." ms - "
                  .(sprintf("%.3f",$right[$symbol]["wt"] / 1000.0))." ms = "
                  .(sprintf("%.3f",$info["wt"] / 1000.0))." ms".
                  "\\nExcl: "
                  .(sprintf("%.3f",$left[$symbol]["excl_wt"] / 1000.0))
                  ." ms - ".(sprintf("%.3f",$right[$symbol]["excl_wt"] / 1000.0))
                   ." ms = ".(sprintf("%.3f",$info["excl_wt"] / 1000.0))." ms".
                  "\\nCalls: ".(sprintf("%.3f",$left[$symbol]["ct"]))." - "
                   .(sprintf("%.3f",$right[$symbol]["ct"]))." = "
                   .(sprintf("%.3f",$info["ct"]))."\"";
      } else if (isset($left[$symbol])) {
        $label = ", label=\"".addslashes($symbol).
                  "\\nInc: ".(sprintf("%.3f",$left[$symbol]["wt"] / 1000.0))
                   ." ms - 0 ms = ".(sprintf("%.3f",$info["wt"] / 1000.0))
                   ." ms"."\\nExcl: "
                   .(sprintf("%.3f",$left[$symbol]["excl_wt"] / 1000.0))
                   ." ms - 0 ms = "
                   .(sprintf("%.3f",$info["excl_wt"] / 1000.0))." ms".
                  "\\nCalls: ".(sprintf("%.3f",$left[$symbol]["ct"]))." - 0 = "
                  .(sprintf("%.3f",$info["ct"]))."\"";
      } else {
        $label = ", label=\"".addslashes($symbol).
                  "\\nInc: 0 ms - "
                  .(sprintf("%.3f",$right[$symbol]["wt"] / 1000.0))
                  ." ms = ".(sprintf("%.3f",$info["wt"] / 1000.0))." ms".
                  "\\nExcl: 0 ms - "
                  .(sprintf("%.3f",$right[$symbol]["excl_wt"] / 1000.0))
                  ." ms = ".(sprintf("%.3f",$info["excl_wt"] / 1000.0))." ms".
                  "\\nCalls: 0 - ".(sprintf("%.3f",$right[$symbol]["ct"]))
                  ." = ".(sprintf("%.3f",$info["ct"]))."\"";
      }
    }
    $result .= "N" . $sym_table[$symbol]["id"];
    $result .= "[shape=$shape ".$label.$width
               .$height.$fontsize.$fillcolor."];\n";
  }

    foreach ($raw_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);

    if (isset($sym_table[$parent]) && isset($sym_table[$child]) &&
        (empty($func) ||
         (!empty($func) && ($parent == $func || $child == $func)))) {

      $label = $info["ct"] == 1 ? $info["ct"]." call" : $info["ct"]." calls";

      $headlabel = $sym_table[$child]["wt"] > 0 ?
                  sprintf("%.1f%%", 100 * $info["wt"]
                                    / $sym_table[$child]["wt"])
                  : "0.0%";

      $taillabel = ($sym_table[$parent]["wt"] > 0) ?
        sprintf("%.1f%%",
                100 * $info["wt"] /
                ($sym_table[$parent]["wt"] - $sym_table["$parent"]["excl_wt"]))
        : "0.0%";

      $linewidth = 1;
      $arrow_size = 1;

      if ($critical_path &&
          isset($path_edges[xhprof_build_parent_child_key($parent, $child)])) {
        $linewidth = 10; $arrow_size = 2;
      }

      $result .= "N" . $sym_table[$parent]["id"] . " -> N"
                 . $sym_table[$child]["id"];
      $result .= "[arrowsize=$arrow_size, style=\"setlinewidth($linewidth)\","
                 ." label=\""
                 .$label."\", headlabel=\"".$headlabel
                 ."\", taillabel=\"".$taillabel."\" ]";
      $result .= ";\n";

    }
  }
  $result = $result . "\n}";

  return $result;
}

function  xhprof_render_diff_image($xhprof_runs_impl, $run1, $run2,
                                   $type, $threshold, $source) {
  $total1;
  $total2;

  $raw_data1 = $xhprof_runs_impl->get_run($run1, $source, $desc_unused);
  $raw_data2 = $xhprof_runs_impl->get_run($run2, $source, $desc_unused);

    $children_table1 = xhprof_get_children_table($raw_data1);
  $children_table2 = xhprof_get_children_table($raw_data2);
  $symbol_tab1 = xhprof_compute_flat_info($raw_data1, $total1);
  $symbol_tab2 = xhprof_compute_flat_info($raw_data2, $total2);
  $run_delta = xhprof_compute_diff($raw_data1, $raw_data2);
  $script = xhprof_generate_dot_script($run_delta, $threshold, $source,
                                       null, null, true,
                                       $symbol_tab1, $symbol_tab2);
  $content = xhprof_generate_image_by_dot($script, $type);

  xhprof_generate_mime_header($type, strlen($content));
  echo $content;
}


function xhprof_get_content_by_run($xhprof_runs_impl, $run_id, $type,
                                   $threshold, $func, $source,
                                   $critical_path) {
  if (!$run_id)
    return "";

  $raw_data = $xhprof_runs_impl->get_run($run_id, $source, $description);
  if (!$raw_data) {
    xhprof_error("Raw data is empty");
    return "";
  }

  $script = xhprof_generate_dot_script($raw_data, $threshold, $source,
                                       $description, $func, $critical_path);

  $content = xhprof_generate_image_by_dot($script, $type);
  return $content;
}


function xhprof_render_image($xhprof_runs_impl, $run_id, $type, $threshold,
                             $func, $source, $critical_path) {

  $content = xhprof_get_content_by_run($xhprof_runs_impl, $run_id, $type,
                                       $threshold,
                                       $func, $source, $critical_path);
  if (!$content) {
    print "Error: either we can not find profile data for run_id ".$run_id
          ." or the threshold ".$threshold." is too small or you do not"
          ." have 'dot' image generation utility installed.";
    exit();
  }

  xhprof_generate_mime_header($type, strlen($content));
  echo $content;
}
